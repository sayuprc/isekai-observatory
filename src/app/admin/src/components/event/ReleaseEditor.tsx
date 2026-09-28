import { Index, Show } from 'solid-js';
import type { EventRelease } from '../../generated';
import { client } from '../../utils/client';
import { createKeywordSearch } from '../keyword-search';
import { KeywordSearchPanel } from '../KeywordSearchPanel';
import { createSortable, reorderItems } from '../sortable';
import { addRelease, releaseLabel, toEventReleases } from './event-links';
import { ListItemActions } from './ListItemActions';

interface ReleaseEditorProps {
  releases: EventRelease[];
  onChange: (updater: (prev: EventRelease[]) => EventRelease[]) => void;
}

export const ReleaseEditor = (props: ReleaseEditorProps) => {
  const moveItem = (fromIndex: number, toIndex: number) => {
    props.onChange((prev) => reorderItems(prev, fromIndex, toIndex));
  };
  const sortable = createSortable((_scope, fromIndex, toIndex) => moveItem(fromIndex, toIndex));

  // 検索の主語はリリースグループなので、ヒットしたグループの版を展開して候補にする
  const releaseSearch = createKeywordSearch<EventRelease>({
    emptyKeywordMessage: 'リリースグループのタイトルを入力してください',
    fetch: async (title) => {
      const { data, status } = await client.api['release-groups'].search.get({
        query: { title, sort: 'first_released_on', order: 'desc', page: 1, per_page: 25 },
      });
      if (!data) {
        return { items: undefined, status };
      }

      const details = await Promise.all(
        data.releaseGroups.map(async (releaseGroup) => {
          const detail = await client.api['release-groups']({ releaseGroupId: releaseGroup.releaseGroupId }).get();
          return { releaseGroup, detail };
        }),
      );
      const failed = details.find(({ detail }) => !detail.data);
      if (failed) {
        return { items: undefined, status: failed.detail.status };
      }

      return {
        items: details.flatMap(({ releaseGroup, detail }) =>
          toEventReleases(releaseGroup, detail.data?.releases ?? []),
        ),
        status,
      };
    },
  });

  return (
    <fieldset class="rounded-box border border-base-300 bg-base-200 p-6">
      <legend class="px-2 text-sm font-semibold text-base-content/70">関連リリース</legend>
      <p class="mb-4 text-sm text-base-content/60">
        ライブ映像作品や特典映像など、このイベントを収録したリリースの版を関連づけます
      </p>

      <div class="mb-6">
        <KeywordSearchPanel
          title="リリースを追加"
          placeholder="リリースグループのタイトルで検索"
          search={releaseSearch}
          itemLabel={releaseLabel}
          onAdd={(release) => props.onChange((prev) => addRelease(prev, release))}
          emptyResultMessage="該当するリリースがありません。リリースの管理画面で先に登録してください"
        />
      </div>

      <Show
        when={props.releases.length > 0}
        fallback={<p class="text-sm text-base-content/60">関連リリースはまだありません</p>}
      >
        <ul class="space-y-2">
          <Index each={props.releases}>
            {(release, index) => (
              <li
                {...sortable.dropTargetProps('releases', index)}
                class="flex items-center justify-between gap-2 rounded-box border border-base-300 bg-base-100 px-4 py-2"
                classList={{
                  'opacity-50': sortable.isDragging('releases', index),
                  'border-primary bg-primary/5': sortable.isDropTarget('releases', index),
                }}
              >
                <div class="flex min-w-0 items-center gap-2">
                  <button {...sortable.dragHandleProps('releases', index, releaseLabel(release()))}>⠿</button>
                  <span class="badge badge-neutral badge-sm">{index + 1}</span>
                  <a
                    class="link link-hover truncate font-medium"
                    href={`/releases/${release().releaseId}`}
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    {releaseLabel(release())}
                  </a>
                  <Show when={!release().isDisplay}>
                    <span class="badge badge-ghost badge-sm">非公開</span>
                  </Show>
                </div>
                <ListItemActions
                  label={releaseLabel(release())}
                  index={index}
                  length={props.releases.length}
                  onMove={moveItem}
                  onRemove={() => props.onChange((prev) => prev.filter((_, i) => i !== index))}
                />
              </li>
            )}
          </Index>
        </ul>
      </Show>
    </fieldset>
  );
};
