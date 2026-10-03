import { Index, Show } from 'solid-js';
import { client } from '../../utils/client';
import { ListItemActions } from '../ListItemActions';
import { SearchCombobox } from '../SearchCombobox';
import { createSortable, reorderItems } from '../sortable';
import { addMember, type MemberForm } from './member-form';

interface MemberEditorProps {
  members: MemberForm[];
  onChange: (updater: (prev: MemberForm[]) => MemberForm[]) => void;
}

const fetchPersons = async (name: string) => {
  const { data, status } = await client.api.persons.search.get({
    query: { name, sort: 'name', order: 'asc', page: 1, per_page: 25 },
  });
  return { items: data?.persons, status };
};

export const MemberEditor = (props: MemberEditorProps) => {
  const moveItem = (fromIndex: number, toIndex: number) => {
    props.onChange((prev) => reorderItems(prev, fromIndex, toIndex));
  };
  const sortable = createSortable((_scope, fromIndex, toIndex) => moveItem(fromIndex, toIndex));

  return (
    <fieldset class="rounded-box border border-base-300 bg-base-200 p-6">
      <legend class="px-2 text-sm font-semibold text-base-content/70">メンバー</legend>
      <p class="mb-4 text-sm text-base-content/60">楽曲披露でグループを選ぶと、この順で共演者に追加されます</p>

      <div class="mb-4 rounded-box border border-base-300 bg-base-100 p-3">
        <SearchCombobox
          label="人物"
          placeholder="人物名を入力"
          fetch={fetchPersons}
          itemLabel={(person) => person.name}
          isAdded={(person) => props.members.some((member) => member.personId === person.personId)}
          onPick={(person) => props.onChange((prev) => addMember(prev, person))}
        />
      </div>

      <Show
        when={props.members.length > 0}
        fallback={<p class="text-sm text-base-content/60">メンバーはまだいません</p>}
      >
        <ul class="space-y-2">
          <Index each={props.members}>
            {(member, index) => (
              <li
                {...sortable.dropTargetProps('members', index)}
                class="flex items-center justify-between gap-2 rounded-box border border-base-300 bg-base-100 px-4 py-2"
                classList={{
                  'opacity-50': sortable.isDragging('members', index),
                  'border-primary bg-primary/5': sortable.isDropTarget('members', index),
                }}
              >
                <div class="flex min-w-0 items-center gap-2">
                  <button {...sortable.dragHandleProps('members', index, member().name)}>⠿</button>
                  <span class="badge badge-neutral badge-sm">{index + 1}</span>
                  <span class="truncate font-medium">{member().name}</span>
                </div>
                <ListItemActions
                  label={member().name}
                  index={index}
                  length={props.members.length}
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
