import { createEffect, createSignal, createUniqueId, For, onCleanup, onMount, Show } from 'solid-js';

export type SelectOption = {
  value: string;
  label: string;
};

interface Props {
  options: SelectOption[];
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
  required?: boolean;
}

export const SearchableSelect = (props: Props) => {
  const [query, setQuery] = createSignal('');
  const [open, setOpen] = createSignal(false);
  const [activeIndex, setActiveIndex] = createSignal(-1);
  const listboxId = createUniqueId();
  const inputId = createUniqueId();
  let containerRef: HTMLDivElement | undefined;

  const selectedLabel = () => props.options.find(option => option.value === props.value)?.label ?? '';

  const filtered = () => {
    const loweredQuery = query().toLowerCase();
    if (loweredQuery === '') {
      return props.options;
    }
    return props.options.filter(o => o.label.toLowerCase().includes(loweredQuery));
  };

  const activeDescendant = () => {
    const index = activeIndex();
    return index >= 0 && index < filtered().length ? `${listboxId}-option-${index}` : undefined;
  };

  const selectOption = (value: string) => {
    props.onChange(value);
    const label = props.options.find(option => option.value === value)?.label ?? '';
    setQuery(label);
    setOpen(false);
    setActiveIndex(-1);
  };

  const handleFocus = () => {
    setQuery('');
    setOpen(true);
    setActiveIndex(-1);
  };

  const handleBlur = (e: FocusEvent) => {
    if (containerRef?.contains(e.relatedTarget as Node)) {
      return;
    }
    setOpen(false);
    setQuery(selectedLabel());
    setActiveIndex(-1);
  };

  const handleKeyDown = (e: KeyboardEvent) => {
    const items = filtered();

    switch (e.key) {
      case 'ArrowDown': {
        e.preventDefault();
        setOpen(true);
        setActiveIndex(prev => (prev < items.length - 1 ? prev + 1 : 0));
        break;
      }
      case 'ArrowUp': {
        e.preventDefault();
        setOpen(true);
        setActiveIndex(prev => (prev > 0 ? prev - 1 : items.length - 1));
        break;
      }
      case 'Enter': {
        e.preventDefault();
        const index = activeIndex();
        const item = items[index];
        if (index >= 0 && item) {
          selectOption(item.value);
        }
        break;
      }
      case 'Escape': {
        setOpen(false);
        setQuery(selectedLabel());
        setActiveIndex(-1);
        break;
      }
    }
  };

  const handleClickOutside = (e: MouseEvent) => {
    if (!containerRef?.contains(e.target as Node)) {
      setOpen(false);
      setQuery(selectedLabel());
      setActiveIndex(-1);
    }
  };

  onMount(() => {
    document.addEventListener('mousedown', handleClickOutside);
    onCleanup(() => {
      document.removeEventListener('mousedown', handleClickOutside);
    });
  });

  createEffect(() => {
    if (!open()) {
      setQuery(selectedLabel());
    }
  });

  return (
    <div ref={el => containerRef = el} class="relative w-full" onFocusOut={handleBlur}>
      <input
        id={inputId}
        type="text"
        class="input input-bordered w-full"
        placeholder={props.placeholder ?? '検索...'}
        value={query()}
        role="combobox"
        aria-autocomplete="list"
        aria-controls={listboxId}
        aria-expanded={open()}
        aria-activedescendant={activeDescendant()}
        onInput={(e) => {
          setQuery(e.currentTarget.value);
          setOpen(true);
          setActiveIndex(-1);
        }}
        onFocus={handleFocus}
        onClick={() => {
          setOpen(true);
          setActiveIndex(-1);
        }}
        onKeyDown={handleKeyDown}
      />
      {props.required && <input type="text" class="hidden" value={props.value} required tabIndex={-1} />}
      <Show when={open()}>
        <ul
          id={listboxId}
          role="listbox"
          aria-labelledby={inputId}
          class="menu bg-base-100 rounded-box shadow-lg absolute z-50 mt-1 max-h-48 w-full overflow-y-auto border border-base-300 p-1"
        >
          <Show
            when={filtered().length > 0}
            fallback={(
              <li class="px-3 py-2 text-sm text-base-content/60" aria-live="polite">
                候補が見つかりません
              </li>
            )}
          >
            <For each={filtered()}>
              {(option, index) => (
                <li>
                  <button
                    id={`${listboxId}-option-${index()}`}
                    type="button"
                    class={index() === activeIndex() ? 'active' : ''}
                    role="option"
                    aria-selected={index() === activeIndex()}
                    onMouseDown={(e) => {
                      e.preventDefault();
                      selectOption(option.value);
                    }}
                  >
                    {option.label}
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
