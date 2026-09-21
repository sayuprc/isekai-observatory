import { onCleanup, onMount } from 'solid-js';

type Props = {
  // 絞り込み対象の entry を指す CSS セレクタ FilterBar に渡すものと揃える
  entrySelector: string;
  // entry を束ねるグループ(年など)を指す CSS セレクタ
  groupSelector: string;
  // グループ内で件数を表示する要素を指す CSS セレクタ
  countSelector: string;
  unit: string;
};

/**
 * グループごとの件数表示を絞り込みに追従させる
 * グループ自体の表示・非表示は CSS の :has() が持つので、ここでは件数だけを書き換える
 */
export default function EntryGroupStatus(props: Props) {
  const updateCounts = () => {
    for (const group of document.querySelectorAll(props.groupSelector)) {
      const countElement = group.querySelector(props.countSelector);

      if (!(countElement instanceof HTMLElement)) {
        continue;
      }

      const matched = Array.from(group.querySelectorAll(props.entrySelector))
        .filter((entry): entry is HTMLElement => entry instanceof HTMLElement)
        .filter(entry => entry.dataset.viewerFilterMatch !== 'false').length;

      countElement.textContent = `${matched}${props.unit}`;
    }
  };

  onMount(() => {
    const onFilterChange = (event: Event) => {
      if (!(event instanceof CustomEvent)) return;
      if (event.detail?.entrySelector !== props.entrySelector) return;
      updateCounts();
    };

    document.addEventListener('viewer:filter-change', onFilterChange);
    onCleanup(() => document.removeEventListener('viewer:filter-change', onFilterChange));
  });

  return null;
}
