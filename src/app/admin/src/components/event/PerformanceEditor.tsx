import { Index, Show, createSignal } from 'solid-js';
import type { Person, SongSummary } from '../../generated';
import { client } from '../../utils/client';
import { createKeywordSearch } from '../keyword-search';
import { KeywordSearchPanel } from '../KeywordSearchPanel';
import { createSortable, reorderItems } from '../sortable';
import { ListItemActions } from './ListItemActions';
import {
  addCoVocalist,
  addPerformance,
  removeCoVocalist,
  setCreditName,
  type PerformanceForm,
} from './performance-form';

interface PerformanceEditorProps {
  performances: PerformanceForm[];
  onChange: (updater: (prev: PerformanceForm[]) => PerformanceForm[]) => void;
  disabled?: boolean;
}

const SEARCH_PER_PAGE = 25;

export const PerformanceEditor = (props: PerformanceEditorProps) => {
  const moveItem = (fromIndex: number, toIndex: number) => {
    props.onChange((prev) => reorderItems(prev, fromIndex, toIndex));
  };
  const sortable = createSortable((_scope, fromIndex, toIndex) => moveItem(fromIndex, toIndex));
  const [personTargetIndex, setPersonTargetIndex] = createSignal(0);

  const songSearch = createKeywordSearch<SongSummary>({
    emptyKeywordMessage: '楽曲名を入力してください',
    fetch: async (title) => {
      const { data, status } = await client.api.songs.search.get({
        query: { title, sort: 'title', order: 'asc', page: 1, per_page: SEARCH_PER_PAGE },
      });
      return { items: data?.songs, status };
    },
  });

  const personSearch = createKeywordSearch<Person>({
    emptyKeywordMessage: '人物名を入力してください',
    fetch: async (name) => {
      const { data, status } = await client.api.persons.search.get({
        query: { name, sort: 'name', order: 'asc', page: 1, per_page: SEARCH_PER_PAGE },
      });
      return { items: data?.persons, status };
    },
  });

  const removePerformance = (index: number) => {
    props.onChange((prev) => prev.filter((_, i) => i !== index));
  };

  const changeCreditName = (performanceIndex: number, personIndex: number, creditName: string) => {
    props.onChange((prev) => setCreditName(prev, performanceIndex, personIndex, creditName));
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
                  <ListItemActions
                    label={`楽曲披露${index + 1}`}
                    index={index}
                    length={props.performances.length}
                    onMove={moveItem}
                    onRemove={() => removePerformance(index)}
                  />
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
                            onInput={(e) => changeCreditName(index, personIndex, e.currentTarget.value)}
                          />
                          <button
                            type="button"
                            class="btn btn-ghost btn-xs text-error"
                            onClick={() => props.onChange((prev) => removeCoVocalist(prev, index, personIndex))}
                          >
                            外す
                          </button>
                        </div>
                      )}
                    </Index>
                  </Show>
                  <button type="button" class="btn btn-ghost btn-xs" onClick={() => setPersonTargetIndex(index)}>
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
        <KeywordSearchPanel
          title="楽曲を追加"
          placeholder="楽曲名で検索"
          search={songSearch}
          itemLabel={(song) => song.title}
          onAdd={(song) => props.onChange((prev) => addPerformance(prev, song))}
          disabled={props.disabled}
          emptyResultMessage="該当する楽曲がありません"
        />
        <KeywordSearchPanel
          title="共演者を追加"
          placeholder="人物名で検索"
          search={personSearch}
          itemLabel={(person) => person.name}
          onAdd={(person) => props.onChange((prev) => addCoVocalist(prev, personTargetIndex(), person))}
          disabled={props.disabled || props.performances.length === 0}
        >
          <p class="mb-2 text-xs text-base-content/60">
            対象: 楽曲披露 {props.performances.length === 0 ? 'なし' : personTargetIndex() + 1}
          </p>
        </KeywordSearchPanel>
      </div>
    </fieldset>
  );
};
