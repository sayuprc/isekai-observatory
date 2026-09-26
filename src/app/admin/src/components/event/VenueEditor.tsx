import { Index, Show } from 'solid-js';
import type { Venue } from '../../generated';
import { client } from '../../utils/client';
import { createKeywordSearch } from '../keyword-search';
import { KeywordSearchPanel } from '../KeywordSearchPanel';
import { createSortable, reorderItems } from '../sortable';
import { addVenue, type VenueEntry } from './event-links';
import { ListItemActions } from './ListItemActions';

interface VenueEditorProps {
  venues: VenueEntry[];
  onChange: (updater: (prev: VenueEntry[]) => VenueEntry[]) => void;
}

export const VenueEditor = (props: VenueEditorProps) => {
  const moveItem = (fromIndex: number, toIndex: number) => {
    props.onChange((prev) => reorderItems(prev, fromIndex, toIndex));
  };
  const sortable = createSortable((_scope, fromIndex, toIndex) => moveItem(fromIndex, toIndex));

  const venueSearch = createKeywordSearch<Venue>({
    emptyKeywordMessage: '開催先名を入力してください',
    fetch: async (name) => {
      const { data, status } = await client.api.venues.search.get({
        query: { name, sort: 'name', order: 'asc', page: 1, per_page: 25 },
      });
      return { items: data?.venues, status };
    },
  });

  return (
    <fieldset class="rounded-box border border-base-300 bg-base-200 p-6">
      <legend class="px-2 text-sm font-semibold text-base-content/70">開催先</legend>
      <p class="mb-4 text-sm text-base-content/60">現地会場と配信先を同じイベントにまとめて登録できます</p>

      <Show
        when={props.venues.length > 0}
        fallback={<p class="text-sm text-base-content/60">開催先はまだありません</p>}
      >
        <ul class="space-y-2">
          <Index each={props.venues}>
            {(venue, index) => (
              <li
                {...sortable.dropTargetProps('venues', index)}
                class="flex items-center justify-between gap-2 rounded-box border border-base-300 bg-base-100 px-4 py-2"
                classList={{
                  'opacity-50': sortable.isDragging('venues', index),
                  'border-primary bg-primary/5': sortable.isDropTarget('venues', index),
                }}
              >
                <div class="flex min-w-0 items-center gap-2">
                  <button {...sortable.dragHandleProps('venues', index, venue().name)}>⠿</button>
                  <span class="badge badge-neutral badge-sm">{index + 1}</span>
                  <span class="truncate font-medium">{venue().name}</span>
                  <span class="badge badge-ghost badge-sm">{venue().kindName}</span>
                </div>
                <ListItemActions
                  label={venue().name}
                  index={index}
                  length={props.venues.length}
                  onMove={moveItem}
                  onRemove={() => props.onChange((prev) => prev.filter((_, i) => i !== index))}
                />
              </li>
            )}
          </Index>
        </ul>
      </Show>

      <div class="mt-6">
        <KeywordSearchPanel
          title="開催先を追加"
          placeholder="開催先名で検索"
          search={venueSearch}
          itemLabel={(venue) => `${venue.name} (${venue.kind.name})`}
          onAdd={(venue) => props.onChange((prev) => addVenue(prev, venue))}
          emptyResultMessage="該当する開催先がありません。開催先の管理画面で先に登録してください"
        />
      </div>
    </fieldset>
  );
};
