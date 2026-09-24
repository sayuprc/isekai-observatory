import { For, Index, Show, createSignal } from 'solid-js';
import type { Person, SongSummary } from '../../generated';
import { client } from '../../utils/client';
import { createSortable, reorderItems } from '../sortable';
import { newId, type PerformanceForm } from './performance-form';

interface PerformanceEditorProps {
  performances: PerformanceForm[];
  onChange: (updater: (prev: PerformanceForm[]) => PerformanceForm[]) => void;
  disabled?: boolean;
}

export const PerformanceEditor = (props: PerformanceEditorProps) => {
  const sortable = createSortable((_scope, fromIndex, toIndex) => {
    props.onChange(prev => reorderItems(prev, fromIndex, toIndex));
  });
  const [searchTitle, setSearchTitle] = createSignal('');
  const [searchResults, setSearchResults] = createSignal<SongSummary[]>([]);
  const [searchError, setSearchError] = createSignal<string | null>(null);
  const [isSearching, setIsSearching] = createSignal(false);
  const [hasSearched, setHasSearched] = createSignal(false);
  const [personQuery, setPersonQuery] = createSignal('');
  const [personResults, setPersonResults] = createSignal<Person[]>([]);
  const [personError, setPersonError] = createSignal<string | null>(null);
  const [personSearching, setPersonSearching] = createSignal(false);
  const [personTargetIndex, setPersonTargetIndex] = createSignal(0);

  const searchSongs = async (event: Event) => {
    event.preventDefault();
    setSearchError(null);
    setHasSearched(true);

    if (searchTitle().trim() === '') {
      setSearchResults([]);
      setSearchError('楽曲名を入力してください');
      return;
    }

    setIsSearching(true);
    const { data, status } = await client.api.songs.search.get({
      query: {
        title: searchTitle().trim(),
        sort: 'title',
        order: 'asc',
        page: 1,
        per_page: 25,
      },
    });
    setIsSearching(false);

    if (status === 401) {
      window.location.href = '/auth/login';
      return;
    }

    if (!data) {
      setSearchError(`検索に失敗しました (${status})`);
      setSearchResults([]);
      return;
    }

    setSearchResults(data.songs);
  };

  const addPerformance = (song: SongSummary) => {
    props.onChange(prev => [
      ...prev,
      {
        performanceId: newId(),
        songId: song.songId,
        songTitle: song.title,
        coVocalists: [],
      },
    ]);
  };

  const removePerformance = (index: number) => {
    props.onChange(prev => prev.filter((_, i) => i !== index));
  };

  const searchPersons = async (event: Event) => {
    event.preventDefault();
    setPersonError(null);

    if (personQuery().trim() === '') {
      setPersonResults([]);
      setPersonError('人物名を入力してください');
      return;
    }

    setPersonSearching(true);
    const { data, status } = await client.api.persons.search.get({
      query: {
        name: personQuery().trim(),
        sort: 'name',
        order: 'asc',
        page: 1,
        per_page: 25,
      },
    });
    setPersonSearching(false);

    if (status === 401) {
      window.location.href = '/auth/login';
      return;
    }

    if (!data) {
      setPersonError(`検索に失敗しました (${status})`);
      setPersonResults([]);
      return;
    }

    setPersonResults(data.persons);
  };

  const addCoVocalist = (person: Person) => {
    const index = personTargetIndex();
    props.onChange(prev =>
      prev.map((performance, i) => {
        if (i !== index) {
          return performance;
        }
        if (performance.coVocalists.some(item => item.personId === person.personId)) {
          return performance;
        }
        return {
          ...performance,
          coVocalists: [
            ...performance.coVocalists,
            { personId: person.personId, name: person.name, creditName: '' },
          ],
        };
      }));
  };

  const setCreditName = (performanceIndex: number, personIndex: number, creditName: string) => {
    props.onChange(prev =>
      prev.map((performance, i) =>
        i === performanceIndex
          ? {
              ...performance,
              coVocalists: performance.coVocalists.map((person, j) =>
                j === personIndex ? { ...person, creditName } : person),
            }
          : performance));
  };

  const removeCoVocalist = (performanceIndex: number, personIndex: number) => {
    props.onChange(prev =>
      prev.map((performance, i) =>
        i === performanceIndex
          ? {
              ...performance,
              coVocalists: performance.coVocalists.filter((_, j) => j !== personIndex),
            }
          : performance));
  };

  return (
    <fieldset class="rounded-box border border-base-300 bg-base-200 p-6" disabled={props.disabled}>
      <legend class="px-2 text-sm font-semibold text-base-content/70">楽曲披露</legend>
      <Show when={props.disabled}>
        <p class="mb-4 text-sm text-base-content/60">延期または中止のイベントには楽曲披露を設定できません</p>
      </Show>

      <Show
        when={props.performances.length > 0}
        fallback={<p class="text-sm text-base-content/60">楽曲披露はまだありません</p>}
      >
        <ul class="space-y-3">
          <Index each={props.performances}>
            {(performance, index) => (
              <li
                {...sortable.dropTargetProps('performances', index)}
                class="rounded-box border border-base-300 bg-base-100 p-4"
                classList={{
                  'opacity-50': sortable.isDragging('performances', index),
                  'border-primary bg-primary/5': sortable.isDropTarget('performances', index),
                }}
              >
                <div class="flex flex-wrap items-center justify-between gap-2">
                  <div class="flex min-w-0 items-center gap-2">
                    <button {...sortable.dragHandleProps('performances', index, `楽曲披露${index + 1}`)}>⠿</button>
                    <span class="badge badge-neutral badge-sm">{index + 1}</span>
                    <a href={`/songs/${performance().songId}`} class="link link-hover truncate font-medium">
                      {performance().songTitle}
                    </a>
                  </div>
                  <div class="flex gap-2">
                    <button
                      type="button"
                      class="btn btn-ghost btn-xs"
                      disabled={index === 0}
                      onClick={() => props.onChange(prev => reorderItems(prev, index, index - 1))}
                    >
                      ↑
                    </button>
                    <button
                      type="button"
                      class="btn btn-ghost btn-xs"
                      disabled={index === props.performances.length - 1}
                      onClick={() => props.onChange(prev => reorderItems(prev, index, index + 1))}
                    >
                      ↓
                    </button>
                    <button
                      type="button"
                      class="btn btn-outline btn-error btn-xs"
                      onClick={() => removePerformance(index)}
                    >
                      削除
                    </button>
                  </div>
                </div>

                <div class="mt-3 space-y-2">
                  <p class="text-xs font-semibold text-base-content/60">共演者</p>
                  <Show
                    when={performance().coVocalists.length > 0}
                    fallback={<p class="text-sm text-base-content/50">共演者なし</p>}
                  >
                    <Index each={performance().coVocalists}>
                      {(person, personIndex) => (
                        <div class="flex flex-wrap items-end gap-2">
                          <span class="text-sm">{person().name}</span>
                          <input
                            type="text"
                            class="input input-bordered input-xs w-40"
                            placeholder="クレジット名(任意)"
                            value={person().creditName}
                            onInput={e => setCreditName(index, personIndex, e.currentTarget.value)}
                          />
                          <button
                            type="button"
                            class="btn btn-ghost btn-xs text-error"
                            onClick={() => removeCoVocalist(index, personIndex)}
                          >
                            外す
                          </button>
                        </div>
                      )}
                    </Index>
                  </Show>
                  <button
                    type="button"
                    class="btn btn-ghost btn-xs"
                    onClick={() => setPersonTargetIndex(index)}
                  >
                    この披露に共演者を追加する対象にする
                    <Show when={personTargetIndex() === index}>
                      <span class="badge badge-primary badge-xs ml-1">選択中</span>
                    </Show>
                  </button>
                </div>
              </li>
            )}
          </Index>
        </ul>
      </Show>

      <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <div class="rounded-box border border-base-300 bg-base-100 p-4">
          <p class="mb-2 text-sm font-semibold">楽曲を追加</p>
          <div class="flex gap-2">
            <input
              type="text"
              class="input input-bordered input-sm min-w-0 flex-1"
              placeholder="楽曲名で検索"
              value={searchTitle()}
              onInput={e => setSearchTitle(e.currentTarget.value)}
              onKeyDown={e => e.key === 'Enter' && searchSongs(e)}
              disabled={props.disabled}
            />
            <button
              type="button"
              class="btn btn-primary btn-sm"
              disabled={props.disabled || isSearching()}
              onClick={searchSongs}
            >
              {isSearching() ? '検索中' : '検索'}
            </button>
          </div>
          <Show when={searchError()}>{message => <p class="mt-2 text-sm text-error">{message()}</p>}</Show>
          <Show when={hasSearched() && !searchError() && searchResults().length === 0}>
            <p class="mt-2 text-sm text-base-content/60">該当する楽曲がありません</p>
          </Show>
          <ul class="mt-3 max-h-48 space-y-1 overflow-y-auto">
            <For each={searchResults()}>
              {song => (
                <li class="flex items-center justify-between gap-2 rounded px-2 py-1 hover:bg-base-200">
                  <span class="truncate text-sm">{song.title}</span>
                  <button
                    type="button"
                    class="btn btn-ghost btn-xs"
                    disabled={props.disabled}
                    onClick={() => addPerformance(song)}
                  >
                    追加
                  </button>
                </li>
              )}
            </For>
          </ul>
        </div>

        <div class="rounded-box border border-base-300 bg-base-100 p-4">
          <p class="mb-2 text-sm font-semibold">共演者を追加</p>
          <p class="mb-2 text-xs text-base-content/60">
            対象: 楽曲披露 {props.performances.length === 0 ? 'なし' : personTargetIndex() + 1}
          </p>
          <div class="flex gap-2">
            <input
              type="text"
              class="input input-bordered input-sm min-w-0 flex-1"
              placeholder="人物名で検索"
              value={personQuery()}
              onInput={e => setPersonQuery(e.currentTarget.value)}
              onKeyDown={e => e.key === 'Enter' && searchPersons(e)}
              disabled={props.disabled || props.performances.length === 0}
            />
            <button
              type="button"
              class="btn btn-primary btn-sm"
              disabled={props.disabled || props.performances.length === 0 || personSearching()}
              onClick={searchPersons}
            >
              {personSearching() ? '検索中' : '検索'}
            </button>
          </div>
          <Show when={personError()}>{message => <p class="mt-2 text-sm text-error">{message()}</p>}</Show>
          <ul class="mt-3 max-h-48 space-y-1 overflow-y-auto">
            <For each={personResults()}>
              {person => (
                <li class="flex items-center justify-between gap-2 rounded px-2 py-1 hover:bg-base-200">
                  <span class="truncate text-sm">{person.name}</span>
                  <button
                    type="button"
                    class="btn btn-ghost btn-xs"
                    disabled={props.disabled || props.performances.length === 0}
                    onClick={() => addCoVocalist(person)}
                  >
                    追加
                  </button>
                </li>
              )}
            </For>
          </ul>
        </div>
      </div>
    </fieldset>
  );
};
