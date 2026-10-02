import { For, Index, Show, createSignal } from 'solid-js';
import type { EventRelease } from '../../generated';
import { client } from '../../utils/client';
import { SearchCombobox } from '../SearchCombobox';
import { createSortable, reorderItems } from '../sortable';
import { CoVocalistChip } from './CoVocalistChip';
import { ListItemActions } from './ListItemActions';
import {
  addCoVocalistToPerformances,
  addPerformance,
  collectCoVocalists,
  moveCoVocalist,
  removeCoVocalist,
  setCreditName,
  type PerformanceForm,
} from './performance-form';
import { clickPerformance, emptySelection } from './performance-selection';
import { addPerformancesFromCandidates } from './release-import';
import { ReleaseImport } from './ReleaseImport';

interface PerformanceEditorProps {
  performances: PerformanceForm[];
  onChange: (updater: (prev: PerformanceForm[]) => PerformanceForm[]) => void;
  relatedReleases: EventRelease[];
  disabled?: boolean;
}

const SEARCH_PER_PAGE = 25;

const fetchSongs = async (title: string) => {
  const { data, status } = await client.api.songs.search.get({
    query: { title, sort: 'title', order: 'asc', page: 1, per_page: SEARCH_PER_PAGE },
  });
  return { items: data?.songs, status };
};

const fetchPersons = async (name: string) => {
  const { data, status } = await client.api.persons.search.get({
    query: { name, sort: 'name', order: 'asc', page: 1, per_page: SEARCH_PER_PAGE },
  });
  return { items: data?.persons, status };
};

export const PerformanceEditor = (props: PerformanceEditorProps) => {
  const moveItem = (fromIndex: number, toIndex: number) => {
    props.onChange((prev) => reorderItems(prev, fromIndex, toIndex));
  };
  const sortable = createSortable((_scope, fromIndex, toIndex) => moveItem(fromIndex, toIndex));
  // 共演者のチップは楽曲披露ごとに並べ替える. 別の披露のチップの上には落とせない
  const chipSortable = createSortable<string>((performanceId, fromIndex, toIndex) => {
    const index = props.performances.findIndex((performance) => performance.performanceId === performanceId);
    props.onChange((prev) => moveCoVocalist(prev, index, fromIndex, toIndex));
  });

  // 共演者の追加先. 並べ替えや削除で位置が変わっても同じ披露を指すよう ID で持つ
  const [selection, setSelection] = createSignal(emptySelection());
  const isSelected = (performanceId: string) => selection().selectedIds.has(performanceId);
  // 削除済みの披露は数えない
  const selectedCount = () => props.performances.filter((performance) => isSelected(performance.performanceId)).length;

  const togglePerformance = (index: number, extendRange: boolean) => {
    const performanceIds = props.performances.map((performance) => performance.performanceId);
    setSelection((current) => clickPerformance(current, performanceIds, index, extendRange));
  };

  const selectAll = () => {
    setSelection({
      selectedIds: new Set(props.performances.map((performance) => performance.performanceId)),
      anchorId: null,
    });
  };

  const addCoVocalistToSelected = (person: { personId: string; name: string }) => {
    props.onChange((prev) => addCoVocalistToPerformances(prev, selection().selectedIds, person));
  };

  // 選択中の披露すべてに付いている人は、追加済みとして扱う
  const isOnAllSelected = (personId: string) =>
    selectedCount() > 0
    && props.performances
      .filter((performance) => isSelected(performance.performanceId))
      .every((performance) => performance.coVocalists.some((coVocalist) => coVocalist.personId === personId));

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

      <div class="mb-4 grid gap-3 rounded-box border border-base-300 bg-base-100 p-3 lg:grid-cols-2">
        <SearchCombobox
          label="楽曲"
          placeholder="楽曲名を入力"
          fetch={fetchSongs}
          itemLabel={(song) => song.title}
          isAdded={(song) => props.performances.some((performance) => performance.songId === song.songId)}
          onPick={(song) => props.onChange((prev) => addPerformance(prev, song))}
          disabled={props.disabled}
        />
        <div class="space-y-2">
          <SearchCombobox
            label="共演者"
            placeholder={selectedCount() === 0 ? '先に一覧で行を選択' : '人物名を入力 (選択中の行に追加)'}
            fetch={fetchPersons}
            itemLabel={(person) => person.name}
            isAdded={(person) => isOnAllSelected(person.personId)}
            onPick={addCoVocalistToSelected}
            disabled={props.disabled || selectedCount() === 0}
          />
          <Show when={collectCoVocalists(props.performances).length > 0}>
            <div class="flex flex-wrap items-center gap-1">
              <span class="text-xs text-base-content/60">このイベントの共演者:</span>
              <For each={collectCoVocalists(props.performances)}>
                {(person) => (
                  <button
                    type="button"
                    class="btn btn-outline btn-xs"
                    aria-label={`${person.name}を選択中の行に追加`}
                    disabled={selectedCount() === 0 || isOnAllSelected(person.personId)}
                    onClick={() => addCoVocalistToSelected(person)}
                  >
                    ＋ {person.name}
                  </button>
                )}
              </For>
            </div>
          </Show>
        </div>
      </div>

      <div class="mb-4">
        <ReleaseImport
          relatedReleases={props.relatedReleases}
          onImport={(candidates) => props.onChange((prev) => addPerformancesFromCandidates(prev, candidates))}
          disabled={props.disabled}
        />
      </div>

      <Show
        when={props.performances.length > 0}
        fallback={<p class="text-sm text-base-content/60">楽曲披露はまだありません</p>}
      >
        <div class="mb-1 flex flex-wrap items-center gap-2 text-xs text-base-content/60">
          <span>{selectedCount()} 件選択中 (行をクリックで選択、Shift+クリックで範囲選択)</span>
          <button type="button" class="btn btn-ghost btn-xs" onClick={selectAll}>
            全選択
          </button>
          <button type="button" class="btn btn-ghost btn-xs" onClick={() => setSelection(emptySelection())}>
            選択解除
          </button>
        </div>
        <ul class="divide-y divide-base-300 rounded-box border border-base-300 bg-base-100 select-none">
          <Index each={props.performances}>
            {(performance, index) => (
              <li
                {...sortable.dropTargetProps('performances', index)}
                class="flex cursor-pointer flex-wrap items-center gap-2 px-2 py-1 transition-colors"
                classList={{
                  'bg-primary/10': isSelected(performance().performanceId),
                  'opacity-50': sortable.isDragging('performances', index),
                  'outline outline-primary': sortable.isDropTarget('performances', index),
                }}
                onClick={(e) => {
                  // 行内のボタンや入力欄の操作では選択を変えない
                  if ((e.target as HTMLElement).closest('button, a, input, select')) {
                    return;
                  }
                  togglePerformance(index, e.shiftKey);
                }}
              >
                <input
                  type="checkbox"
                  class="checkbox checkbox-sm checkbox-primary"
                  aria-label={`${performance().songTitle}を共演者の追加先にする`}
                  checked={isSelected(performance().performanceId)}
                  onClick={(e) => {
                    togglePerformance(index, e.shiftKey);
                    // 選択済みの行を範囲選択に含めたとき、ブラウザが外したチェックを状態に戻す
                    e.currentTarget.checked = isSelected(performance().performanceId);
                  }}
                />
                <button {...sortable.dragHandleProps('performances', index, `楽曲披露${index + 1}`)}>⠿</button>
                <span class="w-6 text-right text-xs text-base-content/60">{index + 1}</span>
                <span class="min-w-40 font-medium">{performance().songTitle}</span>
                <div class="flex flex-1 flex-wrap gap-1">
                  <Index each={performance().coVocalists}>
                    {(person, personIndex) => (
                      <CoVocalistChip
                        name={person().name}
                        creditName={person().creditName}
                        onCreditNameChange={(creditName) => changeCreditName(index, personIndex, creditName)}
                        sortableProps={{
                          ...chipSortable.draggableProps(performance().performanceId, personIndex),
                          ...chipSortable.dropTargetProps(performance().performanceId, personIndex),
                        }}
                        isDragging={chipSortable.isDragging(performance().performanceId, personIndex)}
                        isDropTarget={chipSortable.isDropTarget(performance().performanceId, personIndex)}
                        onRemove={() => props.onChange((prev) => removeCoVocalist(prev, index, personIndex))}
                      />
                    )}
                  </Index>
                </div>
                <a
                  href={`/songs/${performance().songId}`}
                  class="btn btn-ghost btn-xs"
                  target="_blank"
                  rel="noreferrer"
                >
                  楽曲
                </a>
                <ListItemActions
                  label={`楽曲披露${index + 1}`}
                  index={index}
                  length={props.performances.length}
                  onMove={moveItem}
                  onRemove={() => removePerformance(index)}
                />
              </li>
            )}
          </Index>
        </ul>
      </Show>
    </fieldset>
  );
};
