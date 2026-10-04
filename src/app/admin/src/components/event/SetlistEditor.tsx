import { For, Index, Show } from 'solid-js';
import type { EventRelease } from '../../generated';
import { ListItemActions } from '../ListItemActions';
import type { SongCandidate } from '../song-import';
import { createSortable, reorderItems } from '../sortable';
import { appendUnassignedPerformances, newId, type PerformanceForm, type SetlistItemForm } from './performance-form';
import { toReleaseSetlistCandidates } from './release-import';
import { ReleaseImport } from './ReleaseImport';

interface SetlistEditorProps {
  setlist: SetlistItemForm[];
  performances: PerformanceForm[];
  onChange: (updater: (prev: SetlistItemForm[]) => SetlistItemForm[]) => void;
  relatedReleases: EventRelease[];
  // 楽曲披露も同時に増えるので、セットリストだけを更新する onChange とは分ける
  onImport: (candidates: SongCandidate[]) => void;
  // 選択中の楽曲披露。紐づく項目を強調して、披露とセットリストの対応を見やすくする
  highlightedPerformanceIds?: ReadonlySet<string>;
  disabled?: boolean;
}

export const SetlistEditor = (props: SetlistEditorProps) => {
  const moveItem = (fromIndex: number, toIndex: number) => {
    props.onChange((prev) => reorderItems(prev, fromIndex, toIndex));
  };
  const sortable = createSortable((_scope, fromIndex, toIndex) => moveItem(fromIndex, toIndex));

  const unassigned = () => {
    const assignedIds = new Set(props.setlist.flatMap((item) => item.performanceIds));
    return props.performances.filter((performance) => !assignedIds.has(performance.performanceId));
  };

  const songTitleOf = (performanceId: string) =>
    props.performances.find((performance) => performance.performanceId === performanceId)?.songTitle ?? '';

  const isHighlighted = (item: SetlistItemForm) =>
    item.performanceIds.some((performanceId) => props.highlightedPerformanceIds?.has(performanceId));

  const updateItem = (index: number, patch: (item: SetlistItemForm) => SetlistItemForm) => {
    props.onChange((prev) => prev.map((item, i) => (i === index ? patch(item) : item)));
  };

  const addItem = () => {
    props.onChange((prev) => [...prev, { setlistItemId: newId(), label: '', performanceIds: [] }]);
  };

  const removeItem = (index: number) => {
    props.onChange((prev) => prev.filter((_, i) => i !== index));
  };

  const linkPerformance = (index: number, performanceId: string) => {
    updateItem(index, (item) => ({ ...item, performanceIds: [...item.performanceIds, performanceId] }));
  };

  const unlinkPerformance = (index: number, performanceId: string) => {
    updateItem(index, (item) => ({
      ...item,
      performanceIds: item.performanceIds.filter((id) => id !== performanceId),
    }));
  };

  return (
    <fieldset class="rounded-box border border-base-300 bg-base-200 p-6" disabled={props.disabled}>
      <legend class="px-2 text-sm font-semibold text-base-content/70">セットリスト</legend>
      <Show when={props.disabled}>
        <p class="mb-4 text-sm text-base-content/60">延期または中止のイベントにはセットリストを設定できません</p>
      </Show>
      <p class="mb-4 text-sm text-base-content/60">
        ライブまたは配信のみ設定できます。各項目は表示名か楽曲披露のどちらかが必要です
      </p>

      <div class="mb-4 flex flex-wrap gap-2">
        <button
          type="button"
          class="btn btn-primary btn-sm"
          disabled={props.disabled || unassigned().length === 0}
          onClick={() => props.onChange((prev) => appendUnassignedPerformances(prev, props.performances))}
        >
          未紐づけの楽曲披露 {unassigned().length} 件から項目を作成
        </button>
        <button type="button" class="btn btn-outline btn-sm" disabled={props.disabled} onClick={addItem}>
          項目を追加
        </button>
      </div>

      <div class="mb-4">
        <ReleaseImport
          title="リリースの収録楽曲から追加"
          description="選んだリリースの収録楽曲を、曲順のまま 1 曲 1 項目でセットリストの末尾に追加します。楽曲は楽曲披露にも追加し、管理対象外楽曲は表示名だけの項目にします"
          importLabel="セットリストに追加"
          toCandidates={toReleaseSetlistCandidates}
          relatedReleases={props.relatedReleases}
          onImport={props.onImport}
          disabled={props.disabled}
        />
      </div>

      <Show
        when={props.setlist.length > 0}
        fallback={<p class="text-sm text-base-content/60">セットリストはまだありません</p>}
      >
        <ul class="divide-y divide-base-300 rounded-box border border-base-300 bg-base-100">
          <Index each={props.setlist}>
            {(item, index) => (
              <li
                {...sortable.dropTargetProps('setlist', index)}
                class="flex flex-wrap items-center gap-2 px-2 py-1 transition-colors"
                classList={{
                  'bg-info/10 shadow-[inset_3px_0_0_var(--color-info)]': isHighlighted(item()),
                  'opacity-50': sortable.isDragging('setlist', index),
                  'outline outline-primary': sortable.isDropTarget('setlist', index),
                }}
              >
                <button {...sortable.dragHandleProps('setlist', index, `セットリスト${index + 1}`)}>⠿</button>
                <span class="w-6 text-right text-xs text-base-content/60">{index + 1}</span>
                <input
                  type="text"
                  class="input input-bordered input-xs w-36"
                  aria-label={`セットリスト${index + 1}の表示名`}
                  placeholder="表示名(任意)"
                  value={item().label}
                  onInput={(e) => {
                    const label = e.currentTarget.value;
                    updateItem(index, (current) => ({ ...current, label }));
                  }}
                />
                <div class="flex flex-1 flex-wrap items-center gap-1">
                  <For each={item().performanceIds}>
                    {(performanceId) => (
                      <span class="badge badge-primary badge-soft gap-1 whitespace-nowrap">
                        {songTitleOf(performanceId)}
                        <button
                          type="button"
                          aria-label={`${songTitleOf(performanceId)}の紐づけを外す`}
                          onClick={() => unlinkPerformance(index, performanceId)}
                        >
                          ×
                        </button>
                      </span>
                    )}
                  </For>
                  <Show when={unassigned().length > 0}>
                    <select
                      class="select select-ghost select-xs w-44"
                      aria-label={`セットリスト${index + 1}に楽曲披露を紐づける`}
                      value=""
                      onChange={(e) => {
                        const performanceId = e.currentTarget.value;
                        e.currentTarget.value = '';
                        if (performanceId !== '') {
                          linkPerformance(index, performanceId);
                        }
                      }}
                    >
                      <option value="">＋ 楽曲披露を紐づけ</option>
                      <For each={unassigned()}>
                        {(performance) => <option value={performance.performanceId}>{performance.songTitle}</option>}
                      </For>
                    </select>
                  </Show>
                </div>
                <ListItemActions
                  label={`セットリスト${index + 1}`}
                  index={index}
                  length={props.setlist.length}
                  onMove={moveItem}
                  onRemove={() => removeItem(index)}
                />
              </li>
            )}
          </Index>
        </ul>
      </Show>
    </fieldset>
  );
};
