import { For, Show, createEffect, createSignal } from 'solid-js';

type FilterOption = { value: string; label: string };

type TagOption = { tagId: string; name: string };

// rankAttr を省いた並びは SSR の DOM 順を指す
type SortOption = { value: string; label: string; rankAttr?: string };

const ALL_TAGS = 'all';

// 同じ親の中で、元の位置を保ったまま要素だけを並べ替える
// 親ごと append し直すと、フィルター UI など並べ替え対象外の兄弟より後ろへ動いてしまう
function reorderSiblings(elements: HTMLElement[], key: (element: HTMLElement) => number) {
  const byParent = new Map<Element, HTMLElement[]>();

  for (const element of elements) {
    if (!element.parentElement) continue;
    byParent.set(element.parentElement, [...(byParent.get(element.parentElement) ?? []), element]);
  }

  for (const [parent, siblings] of byParent) {
    const next = siblings[siblings.length - 1].nextSibling;
    const sorted = [...siblings].sort((left, right) => key(left) - key(right));

    for (const element of sorted) {
      parent.insertBefore(element, next);
    }
  }
}

type Props = {
  filters: FilterOption[];
  // 対象 entry を絞る CSS セレクタ
  entrySelector: string;
  // entry の dataset から、フィルター値と一致するか判定するキー(複数指定可、いずれかに一致すれば match)
  categoryAttrs: string[];
  // 検索対象テキストを保持する dataset キー(未指定なら検索 UI を出さない)
  searchAttr?: string;
  searchPlaceholder?: string;
  defaultFilter?: string;
  // 選べるタグ(表示順)。空ならタグのセレクトを出さない
  tags?: TagOption[];
  // entry の dataset で、空白区切りのタグ ID を保持するキー
  tagAttr?: string;
  // 選べる並び順。先頭を既定にし、空なら並び替えのセレクトを出さない
  sorts?: SortOption[];
  // entry を束ねるグループ(年など)を指す CSS セレクタ。グループは先頭 entry の順位で並べる
  groupSelector?: string;
};

export default function FilterBar(props: Props) {
  const [filter, setFilter] = createSignal(props.defaultFilter ?? 'all');
  const [query, setQuery] = createSignal('');
  const [tag, setTag] = createSignal(ALL_TAGS);
  const [sort, setSort] = createSignal(props.sorts?.[0]?.value ?? '');
  // 絞り込みのない初回は SSR の DOM と結果が同じなので、全 entry の走査ごと省く
  let firstRun = true;
  // 既定の並びへ戻すため、並べ替える前の DOM 順を覚えておく
  let ssrOrder: Map<HTMLElement, number> | null = null;

  createEffect(() => {
    const option = props.sorts?.find((candidate) => candidate.value === sort());

    if (!option || (ssrOrder === null && !option.rankAttr)) {
      return;
    }

    const entries = Array.from(document.querySelectorAll(props.entrySelector)).filter(
      (entry): entry is HTMLElement => entry instanceof HTMLElement,
    );
    ssrOrder ??= new Map(entries.map((entry, index) => [entry, index]));
    const order = ssrOrder;
    const rankAttr = option.rankAttr;
    const rankOf = (entry: HTMLElement) => (rankAttr ? Number(entry.dataset[rankAttr]) : (order.get(entry) ?? 0));

    reorderSiblings(entries, rankOf);

    if (props.groupSelector) {
      const groups = Array.from(document.querySelectorAll(props.groupSelector)).filter(
        (group): group is HTMLElement => group instanceof HTMLElement,
      );
      const firstEntryRank = (group: HTMLElement) => {
        const first = group.querySelector(props.entrySelector);

        return first instanceof HTMLElement ? rankOf(first) : Number.MAX_SAFE_INTEGER;
      };

      reorderSiblings(groups, firstEntryRank);
    }
  });

  createEffect(() => {
    const f = filter();
    const t = tag();
    const q = query().trim().toLowerCase();
    const skipInitialScan = firstRun && f === 'all' && t === ALL_TAGS && q === '';
    firstRun = false;

    if (skipInitialScan) {
      return;
    }

    const entries = document.querySelectorAll(props.entrySelector);

    for (const entry of entries) {
      if (!(entry instanceof HTMLElement)) continue;
      const categoryMatch = f === 'all' || props.categoryAttrs.some((attr) => entry.dataset[attr] === f);
      const searchKey = props.searchAttr;
      const text = searchKey ? (entry.dataset[searchKey] ?? '') : '';
      const searchMatch = !searchKey || q === '' || text.includes(q);
      const tagKey = props.tagAttr;
      const tagMatch = t === ALL_TAGS || !tagKey || (entry.dataset[tagKey] ?? '').split(' ').includes(t);
      const match = categoryMatch && searchMatch && tagMatch;
      entry.dataset.viewerFilterMatch = match ? 'true' : 'false';
      entry.style.display = match ? '' : 'none';
    }

    document.dispatchEvent(
      new CustomEvent('viewer:filter-change', {
        detail: { entrySelector: props.entrySelector },
      }),
    );
  });

  return (
    <div class="viewer-filters">
      <div class="viewer-select">
        <select aria-label="種別で絞り込む" value={filter()} onChange={(event) => setFilter(event.currentTarget.value)}>
          <For each={props.filters}>{(option) => <option value={option.value}>{option.label}</option>}</For>
        </select>
      </div>
      <Show when={props.tagAttr && props.tags && props.tags.length > 0}>
        <div class="viewer-select">
          <select aria-label="タグで絞り込む" value={tag()} onChange={(event) => setTag(event.currentTarget.value)}>
            <option value={ALL_TAGS}>すべてのタグ</option>
            <For each={props.tags}>{(option) => <option value={option.tagId}>{option.name}</option>}</For>
          </select>
        </div>
      </Show>
      <Show when={props.sorts && props.sorts.length > 0}>
        <div class="viewer-select">
          <select aria-label="並び替える" value={sort()} onChange={(event) => setSort(event.currentTarget.value)}>
            <For each={props.sorts}>{(option) => <option value={option.value}>{option.label}</option>}</For>
          </select>
        </div>
      </Show>
      <Show when={props.searchAttr}>
        <div class="viewer-filters-spacer" />
        <input
          class="viewer-search"
          type="search"
          placeholder={props.searchPlaceholder}
          aria-label={props.searchPlaceholder}
          value={query()}
          onInput={(event) => setQuery(event.currentTarget.value)}
        />
      </Show>
    </div>
  );
}
