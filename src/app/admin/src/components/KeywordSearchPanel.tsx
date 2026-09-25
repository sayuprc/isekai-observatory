import { For, Show, type JSX } from 'solid-js';
import type { KeywordSearch } from './keyword-search';

interface KeywordSearchPanelProps<T extends object> {
  title: string;
  placeholder: string;
  search: KeywordSearch<T>;
  itemLabel: (item: T) => string;
  onAdd: (item: T) => void;
  disabled?: boolean;
  emptyResultMessage?: string;
  children?: JSX.Element;
}

export const KeywordSearchPanel = <T extends object>(props: KeywordSearchPanelProps<T>) => (
  <div class="rounded-box border border-base-300 bg-base-100 p-4">
    <p class="mb-2 text-sm font-semibold">{props.title}</p>
    {props.children}
    <div class="flex gap-2">
      <input
        type="text"
        class="input input-bordered input-sm min-w-0 flex-1"
        placeholder={props.placeholder}
        value={props.search.keyword()}
        onInput={e => props.search.setKeyword(e.currentTarget.value)}
        onKeyDown={e => e.key === 'Enter' && props.search.search(e)}
        disabled={props.disabled}
      />
      <button
        type="button"
        class="btn btn-primary btn-sm"
        disabled={props.disabled || props.search.isSearching()}
        onClick={props.search.search}
      >
        {props.search.isSearching() ? '検索中' : '検索'}
      </button>
    </div>
    <Show when={props.search.error()}>{message => <p class="mt-2 text-sm text-error">{message()}</p>}</Show>
    <Show
      when={props.emptyResultMessage
        && props.search.hasSearched()
        && !props.search.error()
        && props.search.results().length === 0}
    >
      <p class="mt-2 text-sm text-base-content/60">{props.emptyResultMessage}</p>
    </Show>
    <ul class="mt-3 max-h-48 space-y-1 overflow-y-auto">
      <For each={props.search.results()}>
        {item => (
          <li class="flex items-center justify-between gap-2 rounded px-2 py-1 hover:bg-base-200">
            <span class="truncate text-sm">{props.itemLabel(item)}</span>
            <button
              type="button"
              class="btn btn-ghost btn-xs"
              disabled={props.disabled}
              onClick={() => props.onAdd(item)}
            >
              追加
            </button>
          </li>
        )}
      </For>
    </ul>
  </div>
);
