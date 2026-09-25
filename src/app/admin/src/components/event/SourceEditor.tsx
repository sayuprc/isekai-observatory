import { Index, Show } from 'solid-js';
import { createSortable, reorderItems } from '../sortable';
import type { SourceForm } from './event-links';
import { ListItemActions } from './ListItemActions';

interface SourceEditorProps {
  sources: SourceForm[];
  onChange: (updater: (prev: SourceForm[]) => SourceForm[]) => void;
}

export const SourceEditor = (props: SourceEditorProps) => {
  const moveItem = (fromIndex: number, toIndex: number) => {
    props.onChange(prev => reorderItems(prev, fromIndex, toIndex));
  };
  const sortable = createSortable((_scope, fromIndex, toIndex) => moveItem(fromIndex, toIndex));

  const update = (index: number, patch: Partial<SourceForm>) => {
    props.onChange(prev => prev.map((source, i) => (i === index ? { ...source, ...patch } : source)));
  };

  return (
    <fieldset class="rounded-box border border-base-300 bg-base-200 p-6">
      <legend class="px-2 text-sm font-semibold text-base-content/70">出典</legend>
      <p class="mb-4 text-sm text-base-content/60">公式の告知ページや配信ページなど、情報の出どころを登録します</p>

      <Show
        when={props.sources.length > 0}
        fallback={<p class="text-sm text-base-content/60">出典はまだありません</p>}
      >
        <ul class="space-y-3">
          <Index each={props.sources}>
            {(source, index) => (
              <li
                {...sortable.dropTargetProps('sources', index)}
                class="rounded-box border border-base-300 bg-base-100 p-4"
                classList={{
                  'opacity-50': sortable.isDragging('sources', index),
                  'border-primary bg-primary/5': sortable.isDropTarget('sources', index),
                }}
              >
                <div class="flex flex-wrap items-end gap-2">
                  <button {...sortable.dragHandleProps('sources', index, `出典${index + 1}`)}>⠿</button>
                  <span class="badge badge-neutral badge-sm mb-2">{index + 1}</span>
                  <div class="min-w-40 flex-1">
                    <label class="label" for={`source-name-${index}`}>表示名</label>
                    <input
                      id={`source-name-${index}`}
                      class="input input-bordered input-sm w-full"
                      placeholder="公式サイト など"
                      value={source().displayName}
                      onInput={e => update(index, { displayName: e.currentTarget.value })}
                    />
                  </div>
                  <div class="min-w-60 flex-[2]">
                    <label class="label" for={`source-url-${index}`}>URL</label>
                    <input
                      id={`source-url-${index}`}
                      type="url"
                      class="input input-bordered input-sm w-full"
                      placeholder="https://"
                      value={source().url}
                      onInput={e => update(index, { url: e.currentTarget.value })}
                    />
                  </div>
                  <ListItemActions
                    label={`出典${index + 1}`}
                    index={index}
                    length={props.sources.length}
                    onMove={moveItem}
                    onRemove={() => props.onChange(prev => prev.filter((_, i) => i !== index))}
                  />
                </div>
              </li>
            )}
          </Index>
        </ul>
      </Show>

      <div class="mt-4">
        <button
          type="button"
          class="btn btn-outline btn-sm"
          onClick={() => props.onChange(prev => [...prev, { displayName: '', url: '' }])}
        >
          出典を追加
        </button>
      </div>
    </fieldset>
  );
};
