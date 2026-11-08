import { For, Show, type Accessor, type Setter } from 'solid-js';
import type { Media, MediaTypeValue } from '../../generated';
import { MEDIA_TYPE_NAMES } from '../../utils/enum-names';
import { createSortable, reorderItems } from '../sortable';
import { buildMediaDetailUrl, mergeMedia } from './media-utils';
import { MediaCreatePanel } from './MediaCreatePanel';
import { MediaSearchPanel } from './MediaSearchPanel';

export type MediaEntry = {
  mediaId: string;
  title: string;
  url: string;
  type: MediaTypeValue;
  isDisplay: boolean;
};

interface Props {
  entries: Accessor<MediaEntry[]>;
  setEntries: Setter<MediaEntry[]>;
  availableMedia: Accessor<Media[]>;
  setAvailableMedia: Setter<Media[]>;
}

export const toMediaEntry = (item: Pick<Media, 'mediaId' | 'title' | 'url' | 'type' | 'isDisplay'>): MediaEntry => ({
  mediaId: item.mediaId,
  title: item.title,
  url: item.url,
  type: item.type,
  isDisplay: item.isDisplay,
});

export const MediaSection = (props: Props) => {
  const reorderEntries = (fromIndex: number, toIndex: number) => {
    props.setEntries((prev) => reorderItems(prev, fromIndex, toIndex));
  };
  const sortable = createSortable((_scope, fromIndex, toIndex) => reorderEntries(fromIndex, toIndex));

  const selectedIds = () => new Set(props.entries().map((entry) => entry.mediaId));

  const addEntry = (media: Media) => {
    if (selectedIds().has(media.mediaId)) {
      return;
    }

    props.setEntries((prev) => [...prev, toMediaEntry(media)]);
    props.setAvailableMedia((prev) => mergeMedia(prev, [media]));
  };

  const removeEntry = (index: number) => {
    props.setEntries((prev) => prev.filter((_, i) => i !== index));
  };

  return (
    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
      <legend class="px-2 text-sm font-semibold text-base-content/70">メディア</legend>

      <div class="space-y-4">
        <MediaSearchPanel selectedIds={selectedIds} setAvailableMedia={props.setAvailableMedia} onAdd={addEntry} />
        <MediaCreatePanel
          availableMedia={props.availableMedia}
          selectedIds={selectedIds}
          setAvailableMedia={props.setAvailableMedia}
          onAdd={addEntry}
        />
      </div>

      <div class="mt-6">
        <label class="label">選択中のメディア</label>
        <Show
          when={props.entries().length > 0}
          fallback={<p class="text-sm text-base-content/60">メディアはまだ追加されていません。</p>}
        >
          <div class="space-y-3">
            <For each={props.entries()}>
              {(entry, index) => (
                <div
                  {...sortable.dropTargetProps('media', index())}
                  class="rounded-box border border-base-300 bg-base-100 p-4 transition-colors"
                  classList={{
                    'opacity-50': sortable.isDragging('media', index()),
                    'border-primary bg-primary/5': sortable.isDropTarget('media', index()),
                  }}
                >
                  <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-2">
                      <button {...sortable.dragHandleProps('media', index(), entry.title)}>⠿</button>
                      <div class="min-w-0">
                        <p class="font-medium">{entry.title}</p>
                        <p class="text-xs text-base-content/60">{MEDIA_TYPE_NAMES[entry.type]}</p>
                        <a href={entry.url} target="_blank" rel="noreferrer" class="link link-hover break-all text-xs">
                          {entry.url}
                        </a>
                      </div>
                    </div>

                    <div class="flex items-center gap-2">
                      <a
                        href={buildMediaDetailUrl(entry.mediaId)}
                        target="_blank"
                        rel="noreferrer"
                        class="btn btn-ghost btn-xs"
                      >
                        詳細
                      </a>
                      <button
                        type="button"
                        class="btn btn-ghost btn-xs"
                        aria-label={`${entry.title}を上へ移動`}
                        onClick={() => reorderEntries(index(), index() - 1)}
                        disabled={index() === 0}
                      >
                        ↑
                      </button>
                      <button
                        type="button"
                        class="btn btn-ghost btn-xs"
                        aria-label={`${entry.title}を下へ移動`}
                        onClick={() => reorderEntries(index(), index() + 1)}
                        disabled={index() === props.entries().length - 1}
                      >
                        ↓
                      </button>
                      <button
                        type="button"
                        class="btn btn-ghost btn-xs text-error"
                        onClick={() => removeEntry(index())}
                      >
                        削除
                      </button>
                    </div>
                  </div>
                </div>
              )}
            </For>
          </div>
        </Show>
      </div>
    </fieldset>
  );
};
