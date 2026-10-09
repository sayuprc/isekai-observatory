import { For, Match, Show, Switch } from 'solid-js';
import { client } from '../../utils/client';
import { SearchCombobox } from '../SearchCombobox';
import {
  addCoVocalistToPerformances,
  addPersonGroupToPerformances,
  collectCoVocalists,
  collectPersonGroups,
  type PerformanceForm,
  type PersonGroupPreset,
  type SetlistItemForm,
} from './performance-form';
import { emptySelection, type PerformanceSelection } from './performance-selection';

interface PerformanceSelectionPanelProps {
  performances: PerformanceForm[];
  onChange: (updater: (prev: PerformanceForm[]) => PerformanceForm[]) => void;
  setlist: SetlistItemForm[];
  selection: PerformanceSelection;
  onSelectionChange: (updater: (prev: PerformanceSelection) => PerformanceSelection) => void;
  disabled?: boolean;
}

const SEARCH_PER_PAGE = 25;
// 複数選択のとき、曲名を並べる上限
const MAX_LISTED_TITLES = 5;

const fetchPersons = async (name: string) => {
  const { data, status } = await client.api.persons.search.get({
    query: { name, sort: 'name', order: 'asc', page: 1, per_page: SEARCH_PER_PAGE },
  });
  return { items: data?.persons, status };
};

const fetchPersonGroups = async (name: string) => {
  const { data, status } = await client.api['person-groups'].search.get({
    query: { name, page: 1, per_page: SEARCH_PER_PAGE },
  });
  return { items: data?.personGroups, status };
};

// 選択中の楽曲披露の要約と、共演者をまとめて追加する操作
export const PerformanceSelectionPanel = (props: PerformanceSelectionPanelProps) => {
  // 披露を削除しても selectedIds には ID が残るため、selectedIds の件数ではなく現存する披露から数える
  const selected = () =>
    props.performances.filter((performance) => props.selection.selectedIds.has(performance.performanceId));

  const single = () => (selected().length === 1 ? selected()[0] : undefined);

  const setlistNumbersOf = (performanceId: string) =>
    props.setlist.flatMap((item, index) => (item.performanceIds.includes(performanceId) ? [index + 1] : []));

  const selectAll = () => {
    props.onSelectionChange(() => ({
      selectedIds: new Set(props.performances.map((performance) => performance.performanceId)),
      anchorId: null,
    }));
  };

  const addCoVocalistToSelected = (person: { personId: string; name: string }) => {
    props.onChange((prev) => addCoVocalistToPerformances(prev, props.selection.selectedIds, person));
  };

  const addPersonGroupToSelected = (personGroup: PersonGroupPreset) => {
    props.onChange((prev) => addPersonGroupToPerformances(prev, props.selection.selectedIds, personGroup));
  };

  // 選択中の披露すべてに付いている人やグループは、追加済みとして扱う
  const isOnAllSelected = (personId: string) =>
    selected().length > 0
    && selected().every((performance) =>
      performance.coVocalists.some((coVocalist) => coVocalist.personId === personId),
    );

  const isGroupOnAllSelected = (personGroupId: string) =>
    selected().length > 0
    && selected().every((performance) =>
      performance.coVocalists.some((coVocalist) => coVocalist.personGroup?.personGroupId === personGroupId),
    );

  const hasSelection = () => selected().length > 0;

  return (
    <fieldset class="rounded-box border border-base-300 bg-base-200 p-5" disabled={props.disabled}>
      <legend class="px-2 text-sm font-semibold text-base-content/70">選択中の楽曲披露</legend>

      <Switch>
        <Match when={!hasSelection()}>
          <p class="text-sm text-base-content/60">
            楽曲披露の行をクリックで選択、Shift+クリックで範囲選択します。選択した楽曲披露に共演者をまとめて追加できます
          </p>
        </Match>
        <Match when={single()}>
          {(performance) => (
            <div class="space-y-1">
              <p class="text-lg font-semibold break-words">{performance().songTitle}</p>
              <p class="text-sm text-base-content/70">
                <Show when={setlistNumbersOf(performance().performanceId).length > 0} fallback="セットリストに未紐づけ">
                  セットリスト <span class="font-mono">{setlistNumbersOf(performance().performanceId).join(', ')}</span>{' '}
                  に紐づけ済み
                </Show>
              </p>
              <a
                href={`/songs/${performance().songId}`}
                class="link link-hover text-xs"
                target="_blank"
                rel="noreferrer"
              >
                楽曲詳細を開く
              </a>
            </div>
          )}
        </Match>
        <Match when={selected().length > 1}>
          <div class="space-y-1">
            <p class="text-lg font-semibold">{selected().length} 件選択中</p>
            <p class="text-sm break-words text-base-content/70">
              {selected()
                .slice(0, MAX_LISTED_TITLES)
                .map((performance) => performance.songTitle)
                .join(' / ')}
              {selected().length > MAX_LISTED_TITLES ? ` ほか ${selected().length - MAX_LISTED_TITLES} 件` : ''}
            </p>
          </div>
        </Match>
      </Switch>

      <div class="mt-3 flex gap-2">
        <button type="button" class="btn btn-xs" onClick={selectAll} disabled={props.performances.length === 0}>
          全選択
        </button>
        <button
          type="button"
          class="btn btn-xs"
          onClick={() => props.onSelectionChange(() => emptySelection())}
          disabled={!hasSelection()}
        >
          選択解除
        </button>
      </div>

      <div class="mt-5 space-y-3 border-t border-base-300 pt-4">
        <SearchCombobox
          label="共演者"
          placeholder={hasSelection() ? '人物名を入力 (選択中の楽曲披露に追加)' : '先に楽曲披露を選択'}
          fetch={fetchPersons}
          itemLabel={(person) => person.name}
          isAdded={(person) => isOnAllSelected(person.personId)}
          onPick={addCoVocalistToSelected}
          disabled={props.disabled || !hasSelection()}
        />
        <SearchCombobox
          label="グループ"
          placeholder={hasSelection() ? 'グループ名を入力 (メンバーを追加)' : '先に楽曲披露を選択'}
          fetch={fetchPersonGroups}
          itemLabel={(personGroup) => personGroup.name}
          isAdded={(personGroup) => isGroupOnAllSelected(personGroup.personGroupId)}
          onPick={addPersonGroupToSelected}
          disabled={props.disabled || !hasSelection()}
        />
        <Show when={collectPersonGroups(props.performances).length + collectCoVocalists(props.performances).length > 0}>
          <div class="flex flex-wrap items-center gap-1">
            <span class="w-full text-xs text-base-content/60">このイベントの共演者</span>
            <For each={collectPersonGroups(props.performances)}>
              {(personGroup) => (
                <button
                  type="button"
                  class="btn btn-outline btn-secondary btn-xs"
                  aria-label={`${personGroup.name}を選択中の楽曲披露に追加`}
                  disabled={!hasSelection() || isGroupOnAllSelected(personGroup.personGroupId)}
                  onClick={() => addPersonGroupToSelected(personGroup)}
                >
                  ＋ {personGroup.name}
                </button>
              )}
            </For>
            <For each={collectCoVocalists(props.performances)}>
              {(person) => (
                <button
                  type="button"
                  class="btn btn-outline btn-xs"
                  aria-label={`${person.name}を選択中の楽曲披露に追加`}
                  disabled={!hasSelection() || isOnAllSelected(person.personId)}
                  onClick={() => addCoVocalistToSelected(person)}
                >
                  ＋ {person.name}
                </button>
              )}
            </For>
          </div>
        </Show>
      </div>
    </fieldset>
  );
};
