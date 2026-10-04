import { Index, Match, Show, Switch } from 'solid-js';
import type { EventRelease } from '../../generated';
import { client } from '../../utils/client';
import { ListItemActions } from '../ListItemActions';
import { SearchCombobox } from '../SearchCombobox';
import { createSortable, reorderItems } from '../sortable';
import { CoVocalistChip } from './CoVocalistChip';
import {
  addPerformance,
  moveCoVocalist,
  removeCoVocalist,
  setCreditName,
  toCoVocalistUnits,
  type CoVocalistUnit,
  type PerformanceForm,
  type SetlistItemForm,
} from './performance-form';
import { clickPerformance, type PerformanceSelection } from './performance-selection';
import { PersonGroupChip } from './PersonGroupChip';
import { addPerformancesFromCandidates, toReleasePerformanceCandidates } from './release-import';
import { ReleaseImport } from './ReleaseImport';

interface PerformanceEditorProps {
  performances: PerformanceForm[];
  onChange: (updater: (prev: PerformanceForm[]) => PerformanceForm[]) => void;
  relatedReleases: EventRelease[];
  // 選択状態はセットリストの強調や選択パネルと共有するため、呼び出し側で持つ
  selection: PerformanceSelection;
  onSelectionChange: (updater: (prev: PerformanceSelection) => PerformanceSelection) => void;
  // 各行にセットリストの何番に紐づいているかを出すために使う
  setlist: SetlistItemForm[];
  disabled?: boolean;
}

const SEARCH_PER_PAGE = 25;

const fetchSongs = async (title: string) => {
  const { data, status } = await client.api.songs.search.get({
    query: { title, sort: 'title', order: 'asc', page: 1, per_page: SEARCH_PER_PAGE },
  });
  return { items: data?.songs, status };
};

const asGroupUnit = (unit: CoVocalistUnit) => (unit.type === 'group' ? unit : undefined);
const asPersonUnit = (unit: CoVocalistUnit) => (unit.type === 'person' ? unit : undefined);

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

  const isSelected = (performanceId: string) => props.selection.selectedIds.has(performanceId);

  const togglePerformance = (index: number, extendRange: boolean) => {
    const performanceIds = props.performances.map((performance) => performance.performanceId);
    props.onSelectionChange((current) => clickPerformance(current, performanceIds, index, extendRange));
  };

  // 楽曲披露 ID からセットリストの項目番号 (1 始まり) を引く
  const setlistNumbersOf = (performanceId: string) =>
    props.setlist.flatMap((item, index) => (item.performanceIds.includes(performanceId) ? [index + 1] : []));

  const removePerformance = (index: number) => {
    props.onChange((prev) => prev.filter((_, i) => i !== index));
  };

  const changeCreditName = (performanceIndex: number, unitIndex: number, creditName: string) => {
    props.onChange((prev) => setCreditName(prev, performanceIndex, unitIndex, creditName));
  };

  return (
    <fieldset class="rounded-box border border-base-300 bg-base-200 p-6" disabled={props.disabled}>
      <legend class="px-2 text-sm font-semibold text-base-content/70">楽曲披露</legend>
      <Show when={props.disabled}>
        <p class="mb-4 text-sm text-base-content/60">延期または中止のイベントには楽曲披露を設定できません</p>
      </Show>

      <div class="mb-4 rounded-box border border-base-300 bg-base-100 p-3">
        <SearchCombobox
          label="楽曲"
          placeholder="楽曲名を入力"
          fetch={fetchSongs}
          itemLabel={(song) => song.title}
          isAdded={(song) => props.performances.some((performance) => performance.songId === song.songId)}
          onPick={(song) => props.onChange((prev) => addPerformance(prev, song))}
          disabled={props.disabled}
        />
      </div>

      <div class="mb-4">
        <ReleaseImport
          title="リリースの収録楽曲から追加"
          description="選んだリリースの収録楽曲を、曲順のまま楽曲披露の末尾に追加します。管理対象外楽曲は追加できません"
          importLabel="楽曲披露に追加"
          toCandidates={toReleasePerformanceCandidates}
          relatedReleases={props.relatedReleases}
          onImport={(candidates) => props.onChange((prev) => addPerformancesFromCandidates(prev, candidates))}
          disabled={props.disabled}
        />
      </div>

      <Show
        when={props.performances.length > 0}
        fallback={<p class="text-sm text-base-content/60">楽曲披露はまだありません</p>}
      >
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
                  <Index each={toCoVocalistUnits(performance().coVocalists)}>
                    {(unit, unitIndex) => {
                      const sortableProps = () => ({
                        ...chipSortable.draggableProps(performance().performanceId, unitIndex),
                        ...chipSortable.dropTargetProps(performance().performanceId, unitIndex),
                      });
                      const isDragging = () => chipSortable.isDragging(performance().performanceId, unitIndex);
                      const isDropTarget = () => chipSortable.isDropTarget(performance().performanceId, unitIndex);
                      const remove = () => props.onChange((prev) => removeCoVocalist(prev, index, unitIndex));

                      return (
                        <Switch>
                          <Match when={asGroupUnit(unit())}>
                            {(group) => (
                              <PersonGroupChip
                                name={group().personGroup.name}
                                memberNames={group().members.map((member) => member.name)}
                                sortableProps={sortableProps()}
                                isDragging={isDragging()}
                                isDropTarget={isDropTarget()}
                                onRemove={remove}
                              />
                            )}
                          </Match>
                          <Match when={asPersonUnit(unit())}>
                            {(person) => (
                              <CoVocalistChip
                                name={person().coVocalist.name}
                                creditName={person().coVocalist.creditName}
                                onCreditNameChange={(creditName) => changeCreditName(index, unitIndex, creditName)}
                                sortableProps={sortableProps()}
                                isDragging={isDragging()}
                                isDropTarget={isDropTarget()}
                                onRemove={remove}
                              />
                            )}
                          </Match>
                        </Switch>
                      );
                    }}
                  </Index>
                </div>
                <Show when={setlistNumbersOf(performance().performanceId).length > 0}>
                  <span class="font-mono text-xs text-base-content/60">
                    セトリ {setlistNumbersOf(performance().performanceId).join(', ')}
                  </span>
                </Show>
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
