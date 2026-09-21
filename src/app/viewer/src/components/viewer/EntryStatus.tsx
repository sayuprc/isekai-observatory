import { createSignal, onCleanup, onMount } from 'solid-js';

type Props = {
  entrySelector: string;
  emptySelector?: string;
  // SSR 時点の件数 hydration が遅れても 0 件と表示しないために渡す
  initialCount?: number;
};

function countMatchedEntries(selector: string): number {
  return Array.from(document.querySelectorAll(selector))
    .filter((entry): entry is HTMLElement => entry instanceof HTMLElement)
    .filter(entry => entry.dataset.viewerFilterMatch !== 'false').length;
}

export default function EntryStatus(props: Props) {
  const [count, setCount] = createSignal(props.initialCount ?? 0);

  const updateCount = () => {
    const nextCount = countMatchedEntries(props.entrySelector);
    setCount(nextCount);

    if (!props.emptySelector) return;

    const emptyElements = document.querySelectorAll(props.emptySelector);
    for (const emptyElement of emptyElements) {
      if (emptyElement instanceof HTMLElement) {
        emptyElement.hidden = nextCount !== 0;
      }
    }
  };

  onMount(() => {
    updateCount();

    const onFilterChange = (event: Event) => {
      if (!(event instanceof CustomEvent)) return;
      if (event.detail?.entrySelector !== props.entrySelector) return;
      updateCount();
    };

    document.addEventListener('viewer:filter-change', onFilterChange);
    onCleanup(() => document.removeEventListener('viewer:filter-change', onFilterChange));
  });

  return (
    <div class="entry-status">
      {count()} 件
    </div>
  );
}
