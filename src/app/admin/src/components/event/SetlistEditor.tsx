import { For, Index, Show } from 'solid-js';
import { createSortable, reorderItems } from '../sortable';
import { newId, type PerformanceForm, type SetlistItemForm } from './performance-form';

interface SetlistEditorProps {
  setlist: SetlistItemForm[];
  performances: PerformanceForm[];
  onChange: (updater: (prev: SetlistItemForm[]) => SetlistItemForm[]) => void;
  disabled?: boolean;
}

export const SetlistEditor = (props: SetlistEditorProps) => {
  const sortable = createSortable((_scope, fromIndex, toIndex) => {
    props.onChange(prev => reorderItems(prev, fromIndex, toIndex));
  });

  const referencedIds = () => new Set(props.setlist.flatMap(item => item.performanceIds));

  const availableFor = (itemIndex: number) => {
    const current = new Set(props.setlist[itemIndex]?.performanceIds ?? []);
    return props.performances.filter(
      performance => current.has(performance.performanceId) || !referencedIds().has(performance.performanceId),
    );
  };

  const addItem = () => {
    props.onChange(prev => [...prev, { setlistItemId: newId(), label: '', performanceIds: [] }]);
  };

  const removeItem = (index: number) => {
    props.onChange(prev => prev.filter((_, i) => i !== index));
  };

  const setLabel = (index: number, label: string) => {
    props.onChange(prev => prev.map((item, i) => (i === index ? { ...item, label } : item)));
  };

  const togglePerformance = (itemIndex: number, performanceId: string, checked: boolean) => {
    props.onChange(prev =>
      prev.map((item, i) => {
        if (i !== itemIndex) {
          return item;
        }
        if (checked) {
          return item.performanceIds.includes(performanceId)
            ? item
            : { ...item, performanceIds: [...item.performanceIds, performanceId] };
        }
        return { ...item, performanceIds: item.performanceIds.filter(id => id !== performanceId) };
      }));
  };

  return (
    <fieldset class="rounded-box border border-base-300 bg-base-200 p-6" disabled={props.disabled}>
      <legend class="px-2 text-sm font-semibold text-base-content/70">セットリスト</legend>
      <Show when={props.disabled}>
        <p class="mb-4 text-sm text-base-content/60">延期または中止のイベントにはセットリストを設定できません</p>
      </Show>
      <p class="mb-4 text-sm text-base-content/60">ライブまたは配信のみ設定できます。各項目は表示名か楽曲披露のどちらかが必要です</p>

      <Show
        when={props.setlist.length > 0}
        fallback={<p class="text-sm text-base-content/60">セットリストはまだありません</p>}
      >
        <ul class="space-y-3">
          <Index each={props.setlist}>
            {(item, index) => (
              <li
                {...sortable.dropTargetProps('setlist', index)}
                class="rounded-box border border-base-300 bg-base-100 p-4"
                classList={{
                  'opacity-50': sortable.isDragging('setlist', index),
                  'border-primary bg-primary/5': sortable.isDropTarget('setlist', index),
                }}
              >
                <div class="flex flex-wrap items-end justify-between gap-2">
                  <div class="flex flex-wrap items-end gap-2">
                    <button {...sortable.dragHandleProps('setlist', index, `セットリスト${index + 1}`)}>⠿</button>
                    <span class="badge badge-neutral badge-sm mb-2">{index + 1}</span>
                    <div>
                      <label class="label" for={`setlist-label-${index}`}>表示名(任意)</label>
                      <input
                        id={`setlist-label-${index}`}
                        type="text"
                        class="input input-bordered input-sm"
                        value={item().label}
                        placeholder="MC / アンコール など"
                        onInput={e => setLabel(index, e.currentTarget.value)}
                      />
                    </div>
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
                      disabled={index === props.setlist.length - 1}
                      onClick={() => props.onChange(prev => reorderItems(prev, index, index + 1))}
                    >
                      ↓
                    </button>
                    <button type="button" class="btn btn-outline btn-error btn-xs" onClick={() => removeItem(index)}>
                      削除
                    </button>
                  </div>
                </div>

                <div class="mt-3">
                  <p class="mb-2 text-xs font-semibold text-base-content/60">紐づける楽曲披露</p>
                  <Show
                    when={availableFor(index).length > 0}
                    fallback={<p class="text-sm text-base-content/50">紐づけ可能な楽曲披露がありません</p>}
                  >
                    <ul class="space-y-1">
                      <For each={availableFor(index)}>
                        {performance => (
                          <li>
                            <label class="flex cursor-pointer items-center gap-2 text-sm">
                              <input
                                type="checkbox"
                                class="checkbox checkbox-sm"
                                checked={item().performanceIds.includes(performance.performanceId)}
                                onChange={e =>
                                  togglePerformance(index, performance.performanceId, e.currentTarget.checked)}
                              />
                              {performance.songTitle}
                            </label>
                          </li>
                        )}
                      </For>
                    </ul>
                  </Show>
                </div>
              </li>
            )}
          </Index>
        </ul>
      </Show>

      <div class="mt-4">
        <button type="button" class="btn btn-outline btn-sm" disabled={props.disabled} onClick={addItem}>
          項目を追加
        </button>
      </div>
    </fieldset>
  );
};
