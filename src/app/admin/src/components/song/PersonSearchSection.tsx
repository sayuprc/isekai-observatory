import { createSignal, For, Show } from 'solid-js';
import type { Accessor, Setter } from 'solid-js';
import type { Person, SongPersonRole } from '../../generated';
import { client } from '../../utils/client';
import { createSortable, reorderItems } from '../sortable';
import { addSelectedPersonToRole, type PersonSelections } from './person-selection';

interface Props {
  selections: Accessor<PersonSelections>;
  setSelections: Setter<PersonSelections>;
}

const PER_PAGE = 25;
const ROLE_OPTIONS: { role: SongPersonRole; label: string }[] = [
  { role: 1, label: '作詞' },
  { role: 2, label: '作曲' },
  { role: 3, label: '編曲' },
];

export const PersonSearchSection = (props: Props) => {
  const reorderPersons = (role: SongPersonRole, fromIndex: number, toIndex: number) => {
    props.setSelections(current => ({
      ...current,
      [role]: reorderItems(current[role], fromIndex, toIndex),
    }));
  };
  const sortable = createSortable<SongPersonRole>(reorderPersons);
  const [query, setQuery] = createSignal('');
  const [searchedName, setSearchedName] = createSignal('');
  const [results, setResults] = createSignal<Person[]>([]);
  const [page, setPage] = createSignal(1);
  const [maxPage, setMaxPage] = createSignal(1);
  const [searching, setSearching] = createSignal(false);
  const [hasSearched, setHasSearched] = createSignal(false);
  const [searchError, setSearchError] = createSignal<string | null>(null);

  const isSelected = (role: SongPersonRole, personId: string) =>
    props.selections()[role].some(person => person.personId === personId);

  const search = async (name: string, nextPage = 1) => {
    if (searching()) {
      return;
    }

    if (name === '') {
      setSearchError('人物名を入力してください');
      setResults([]);
      setHasSearched(false);
      return;
    }

    setSearching(true);
    setSearchError(null);
    setHasSearched(true);
    setSearchedName(name);

    const { data, status } = await client.api.persons.search.get({
      query: {
        name,
        sort: 'name',
        order: 'asc',
        page: nextPage,
        per_page: PER_PAGE,
      },
    });

    setSearching(false);

    if (status === 401) {
      window.location.href = '/auth/login';
      return;
    }

    if (!data) {
      setSearchError(`検索に失敗しました (${status})`);
      setResults([]);
      return;
    }

    setResults(data.persons);
    setPage(nextPage);
    setMaxPage(data.maxPage);
  };

  const togglePerson = (role: SongPersonRole, person: Person) => {
    if (isSelected(role, person.personId)) {
      removePerson(role, person.personId);
      return;
    }

    props.setSelections(current =>
      addSelectedPersonToRole(current, role, { personId: person.personId, name: person.name }));
  };

  const removePerson = (role: SongPersonRole, personId: string) => {
    props.setSelections(current => ({
      ...current,
      [role]: current[role].filter(person => person.personId !== personId),
    }));
  };

  return (
    <section class="space-y-4 rounded-box border border-base-300 bg-base-100 p-4">
      <div>
        <label class="label" for="song-person-search">人物名</label>
        <div class="flex gap-2">
          <input
            id="song-person-search"
            type="text"
            class="input input-bordered min-w-0 flex-1"
            value={query()}
            placeholder="人物名で検索"
            onInput={event => setQuery(event.currentTarget.value)}
            onKeyDown={(event) => {
              if (event.key === 'Enter') {
                event.preventDefault();
                void search(query().trim());
              }
            }}
          />
          <button
            type="button"
            class="btn btn-outline"
            disabled={searching()}
            onClick={() => void search(query().trim())}
          >
            {searching() ? '検索中...' : '検索'}
          </button>
        </div>
        <Show when={searchError()}>{message => <p class="mt-2 text-sm text-error">{message()}</p>}</Show>
      </div>

      <Show when={hasSearched() && !searchError()}>
        <div class="space-y-2">
          <Show
            when={results().length > 0}
            fallback={<p class="text-sm text-base-content/60">条件に一致する人物はありません。</p>}
          >
            <For each={results()}>
              {person => (
                <div class="flex items-center justify-between gap-3 rounded-box border border-base-300 p-3">
                  <span class="min-w-0 truncate">{person.name}</span>
                  <div class="join shrink-0">
                    <For each={ROLE_OPTIONS}>
                      {option => (
                        <button
                          type="button"
                          class="btn btn-xs join-item"
                          classList={{
                            'btn-primary': isSelected(option.role, person.personId),
                            'btn-outline': !isSelected(option.role, person.personId),
                          }}
                          aria-pressed={isSelected(option.role, person.personId)}
                          onClick={() => togglePerson(option.role, person)}
                        >
                          {option.label}
                        </button>
                      )}
                    </For>
                  </div>
                </div>
              )}
            </For>
          </Show>

          <Show when={maxPage() > 1}>
            <div class="flex items-center justify-between text-xs text-base-content/60">
              <span>{page()} / {maxPage()} ページ</span>
              <div class="flex gap-2">
                <button
                  type="button"
                  class="btn btn-ghost btn-xs"
                  disabled={searching() || page() <= 1}
                  onClick={() => void search(searchedName(), page() - 1)}
                >
                  前へ
                </button>
                <button
                  type="button"
                  class="btn btn-ghost btn-xs"
                  disabled={searching() || page() >= maxPage()}
                  onClick={() => void search(searchedName(), page() + 1)}
                >
                  次へ
                </button>
              </div>
            </div>
          </Show>
        </div>
      </Show>

      <div class="grid gap-3 lg:grid-cols-3">
        <For each={ROLE_OPTIONS}>
          {option => (
            <div class="rounded-box border border-base-300 p-3">
              <p class="font-semibold">{option.label}</p>
              <Show
                when={props.selections()[option.role].length > 0}
                fallback={<p class="mt-2 text-sm text-base-content/60">まだ追加されていません。</p>}
              >
                <div class="mt-2 space-y-2">
                  <For each={props.selections()[option.role]}>
                    {(person, index) => (
                      <div
                        {...sortable.dropTargetProps(option.role, index())}
                        class="flex items-center justify-between gap-3 rounded-box border border-base-300 p-3 transition-colors"
                        classList={{
                          'opacity-50': sortable.isDragging(option.role, index()),
                          'border-primary bg-primary/5': sortable.isDropTarget(option.role, index()),
                        }}
                      >
                        <div class="flex min-w-0 items-center gap-2">
                          <button {...sortable.dragHandleProps(option.role, index(), person.name)}>⠿</button>
                          <span class="min-w-0 truncate">{person.name}</span>
                        </div>
                        <div class="flex shrink-0 gap-1">
                          <button
                            type="button"
                            class="btn btn-ghost btn-xs"
                            aria-label={`${person.name}を上へ移動`}
                            disabled={index() === 0}
                            onClick={() => reorderPersons(option.role, index(), index() - 1)}
                          >
                            ↑
                          </button>
                          <button
                            type="button"
                            class="btn btn-ghost btn-xs"
                            aria-label={`${person.name}を下へ移動`}
                            disabled={index() === props.selections()[option.role].length - 1}
                            onClick={() => reorderPersons(option.role, index(), index() + 1)}
                          >
                            ↓
                          </button>
                          <button
                            type="button"
                            class="btn btn-ghost btn-xs text-error"
                            onClick={() => removePerson(option.role, person.personId)}
                          >
                            削除
                          </button>
                        </div>
                      </div>
                    )}
                  </For>
                </div>
              </Show>
            </div>
          )}
        </For>
      </div>
    </section>
  );
};
