import { For, Show, createEffect, createSignal } from 'solid-js';

type FilterOption = { value: string; label: string };

type TagOption = { tagId: string; name: string };

const ALL_TAGS = 'all';

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
};

export default function FilterBar(props: Props) {
  const [filter, setFilter] = createSignal(props.defaultFilter ?? 'all');
  const [query, setQuery] = createSignal('');
  const [tag, setTag] = createSignal(ALL_TAGS);
  // 絞り込みのない初回は SSR の DOM と結果が同じなので、全 entry の走査ごと省く
  let firstRun = true;

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
