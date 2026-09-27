import { Index, Show, createSignal } from 'solid-js';
import { client } from '../../utils/client';
import { SearchCombobox } from '../SearchCombobox';
import { createSortable, reorderItems } from '../sortable';
import { CoVocalistChip } from './CoVocalistChip';
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

  // 並べ替えや削除で位置が変わっても同じ披露を指すよう、追加先は ID で持つ
  const [targetId, setTargetId] = createSignal<string | null>(null);
  const targetIndex = () => {
    const index = props.performances.findIndex((performance) => performance.performanceId === targetId());
    return index === -1 ? 0 : index;
  };
  const target = () => props.performances[targetIndex()];

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
        <SearchCombobox
          label="共演者"
          placeholder="人物名を入力 (選択中の行に追加)"
          fetch={fetchPersons}
          itemLabel={(person) => person.name}
          isAdded={(person) =>
            target()?.coVocalists.some((coVocalist) => coVocalist.personId === person.personId) ?? false
          }
          onPick={(person) => props.onChange((prev) => addCoVocalist(prev, targetIndex(), person))}
          disabled={props.disabled || props.performances.length === 0}
        />
      </div>

      <Show
        when={props.performances.length > 0}
        fallback={<p class="text-sm text-base-content/60">楽曲披露はまだありません</p>}
      >
        <p class="mb-1 text-xs text-base-content/60">行をクリックすると共演者の追加先になります</p>
        <ul class="divide-y divide-base-300 rounded-box border border-base-300 bg-base-100">
          <Index each={props.performances}>
            {(performance, index) => (
              <li
                {...sortable.dropTargetProps('performances', index)}
                class="flex cursor-pointer flex-wrap items-center gap-2 px-2 py-1 transition-colors"
                classList={{
                  'bg-primary/10': targetIndex() === index,
                  'opacity-50': sortable.isDragging('performances', index),
                  'outline outline-primary': sortable.isDropTarget('performances', index),
                }}
                onClick={() => setTargetId(performance().performanceId)}
              >
                <button {...sortable.dragHandleProps('performances', index, `楽曲披露${index + 1}`)}>⠿</button>
                <span class="w-6 text-right text-xs text-base-content/60">{index + 1}</span>
                <button
                  type="button"
                  class="min-w-40 text-left font-medium"
                  aria-pressed={targetIndex() === index}
                  title="共演者の追加先にする"
                >
                  {performance().songTitle}
                </button>
                <div class="flex flex-1 flex-wrap gap-1">
                  <Index each={performance().coVocalists}>
                    {(person, personIndex) => (
                      <CoVocalistChip
                        name={person().name}
                        creditName={person().creditName}
                        onCreditNameChange={(creditName) => changeCreditName(index, personIndex, creditName)}
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
