import { For, Show, createSignal, createUniqueId, onCleanup } from 'solid-js';
import { redirectToLogin } from '../utils/auth-redirect';

const DEBOUNCE_MS = 250;

interface SearchComboboxProps<T extends object> {
  label: string;
  placeholder: string;
  fetch: (keyword: string) => Promise<{ items: T[] | undefined; status: number }>;
  itemLabel: (item: T) => string;
  onPick: (item: T) => void;
  isAdded?: (item: T) => boolean;
  disabled?: boolean;
}

// 入力に合わせて候補を縦に出す検索欄. 選ぶと入力を空にしてフォーカスを残し、続けて追加できる
export const SearchCombobox = <T extends object>(props: SearchComboboxProps<T>) => {
  const inputId = createUniqueId();
  const listboxId = createUniqueId();
  const [keyword, setKeyword] = createSignal('');
  const [results, setResults] = createSignal<T[]>([]);
  const [open, setOpen] = createSignal(false);
  const [activeIndex, setActiveIndex] = createSignal(0);
  const [isSearching, setIsSearching] = createSignal(false);
  const [error, setError] = createSignal<string | null>(null);
  let timer: ReturnType<typeof setTimeout> | undefined;
  let latestRequest = 0;
  let inputRef: HTMLInputElement | undefined;

  onCleanup(() => clearTimeout(timer));

  const optionId = (index: number) => `${listboxId}-option-${index}`;

  const search = async (value: string) => {
    const request = ++latestRequest;
    setIsSearching(true);
    const { items, status } = await props.fetch(value);

    // 入力が進んだあとに届いた古い結果は捨てる
    if (request !== latestRequest) {
      return;
    }

    setIsSearching(false);

    if (status === 401) {
      redirectToLogin();
      return;
    }

    if (!items) {
      setError(`検索に失敗しました (${status})`);
      setResults([]);
      setOpen(false);
      return;
    }

    setError(null);
    setResults(items);
    setActiveIndex(0);
    setOpen(true);
  };

  const changeKeyword = (value: string) => {
    setKeyword(value);
    clearTimeout(timer);

    const trimmed = value.trim();
    if (trimmed === '') {
      latestRequest++;
      setIsSearching(false);
      setError(null);
      setResults([]);
      setOpen(false);
      return;
    }

    timer = setTimeout(() => void search(trimmed), DEBOUNCE_MS);
  };

  const pick = (item: T) => {
    props.onPick(item);
    setKeyword('');
    setResults([]);
    setOpen(false);
    inputRef?.focus();
  };

  const moveActive = (step: number) => {
    const length = results().length;
    if (length === 0) {
      return;
    }
    setOpen(true);
    setActiveIndex((index) => (index + step + length) % length);
  };

  const handleKeyDown = (e: KeyboardEvent) => {
    switch (e.key) {
      case 'ArrowDown': {
        e.preventDefault();
        moveActive(1);
        break;
      }
      case 'ArrowUp': {
        e.preventDefault();
        moveActive(-1);
        break;
      }
      case 'Enter': {
        e.preventDefault();
        const item = results()[activeIndex()];
        if (open() && item) {
          pick(item);
        }
        break;
      }
      case 'Escape': {
        setOpen(false);
        break;
      }
    }
  };

  return (
    <div
      class="relative"
      onFocusOut={(e) => {
        if (!e.currentTarget.contains(e.relatedTarget as Node)) {
          setOpen(false);
        }
      }}
    >
      <label class="label mb-1 text-sm" for={inputId}>
        {props.label}
      </label>
      <div class="input input-bordered input-sm flex w-full items-center gap-2">
        <input
          ref={(el) => (inputRef = el)}
          id={inputId}
          type="text"
          class="min-w-0 grow"
          role="combobox"
          aria-autocomplete="list"
          aria-controls={listboxId}
          aria-expanded={open()}
          aria-activedescendant={open() && results().length > 0 ? optionId(activeIndex()) : undefined}
          placeholder={props.placeholder}
          value={keyword()}
          disabled={props.disabled}
          onInput={(e) => changeKeyword(e.currentTarget.value)}
          onFocus={() => setOpen(results().length > 0)}
          onKeyDown={handleKeyDown}
        />
        <Show when={isSearching()}>
          <span class="loading loading-spinner loading-xs" aria-label="検索中" />
        </Show>
      </div>
      <Show when={error()}>{(message) => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>
      <Show when={open()}>
        <ul
          id={listboxId}
          role="listbox"
          aria-labelledby={inputId}
          class="menu menu-sm absolute z-50 mt-1 max-h-72 w-full flex-nowrap overflow-y-auto rounded-box border border-base-300 bg-base-100 p-1 shadow-lg"
        >
          <Show
            when={results().length > 0}
            fallback={
              <li class="px-3 py-2 text-sm text-base-content/60" aria-live="polite">
                候補が見つかりません
              </li>
            }
          >
            <For each={results()}>
              {(item, index) => (
                <li>
                  <button
                    id={optionId(index())}
                    type="button"
                    role="option"
                    tabIndex={-1}
                    aria-selected={index() === activeIndex()}
                    class="flex justify-between"
                    classList={{ 'menu-active': index() === activeIndex() }}
                    onMouseEnter={() => setActiveIndex(index())}
                    onMouseDown={(e) => {
                      e.preventDefault();
                      pick(item);
                    }}
                  >
                    <span class="truncate">{props.itemLabel(item)}</span>
                    <Show when={props.isAdded?.(item)}>
                      <span class="badge badge-ghost badge-xs shrink-0">追加済み</span>
                    </Show>
                  </button>
                </li>
              )}
            </For>
          </Show>
        </ul>
      </Show>
    </div>
  );
};
