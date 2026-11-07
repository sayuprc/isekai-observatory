import { For, createSignal, createUniqueId, onCleanup, onMount } from 'solid-js';

type Option = { value: string; label: string };

type Props = {
  label: string;
  options: Option[];
  value: string;
  onChange: (value: string) => void;
};

// ネイティブ select は開いた一覧の見た目を CSS で変えられないので、listbox を自前で組む
export default function ViewerSelect(props: Props) {
  const listId = createUniqueId();
  const [open, setOpen] = createSignal(false);
  const [active, setActive] = createSignal(0);
  let root: HTMLDivElement | undefined;
  let list: HTMLUListElement | undefined;

  const selectedIndex = () =>
    Math.max(
      0,
      props.options.findIndex((option) => option.value === props.value),
    );
  const selectedLabel = () => props.options[selectedIndex()]?.label ?? '';
  const optionId = (index: number) => `${listId}-${index}`;

  const openList = () => {
    setActive(selectedIndex());
    setOpen(true);
    queueMicrotask(() => scrollActiveIntoView());
  };

  const scrollActiveIntoView = () => {
    list?.children[active()]?.scrollIntoView({ block: 'nearest' });
  };

  const move = (delta: number) => {
    const last = props.options.length - 1;
    setActive(Math.min(last, Math.max(0, active() + delta)));
    scrollActiveIntoView();
  };

  const choose = (index: number) => {
    props.onChange(props.options[index].value);
    setOpen(false);
  };

  const onKeyDown = (event: KeyboardEvent) => {
    if (!open()) {
      if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
        event.preventDefault();
        openList();
      }
      return;
    }

    switch (event.key) {
      case 'ArrowDown':
        event.preventDefault();
        move(1);
        break;
      case 'ArrowUp':
        event.preventDefault();
        move(-1);
        break;
      case 'Home':
        event.preventDefault();
        setActive(0);
        scrollActiveIntoView();
        break;
      case 'End':
        event.preventDefault();
        setActive(props.options.length - 1);
        scrollActiveIntoView();
        break;
      case 'Enter':
      case ' ':
        event.preventDefault();
        choose(active());
        break;
      case 'Escape':
        event.preventDefault();
        setOpen(false);
        break;
      case 'Tab':
        setOpen(false);
        break;
    }
  };

  // client:load はサーバーでも描画されるので、document への登録は onMount で行う
  onMount(() => {
    const onPointerDown = (event: PointerEvent) => {
      if (open() && root && !root.contains(event.target as Node)) setOpen(false);
    };

    document.addEventListener('pointerdown', onPointerDown);
    onCleanup(() => document.removeEventListener('pointerdown', onPointerDown));
  });

  return (
    <div class="viewer-select" ref={(element) => (root = element)}>
      <button
        type="button"
        class="viewer-select-trigger"
        role="combobox"
        aria-label={props.label}
        aria-haspopup="listbox"
        aria-expanded={open()}
        aria-controls={listId}
        aria-activedescendant={open() ? optionId(active()) : undefined}
        onClick={() => (open() ? setOpen(false) : openList())}
        onKeyDown={onKeyDown}
      >
        {selectedLabel()}
      </button>
      <ul
        id={listId}
        class="viewer-select-list"
        role="listbox"
        aria-label={props.label}
        hidden={!open()}
        ref={(element) => (list = element)}
      >
        <For each={props.options}>
          {(option, index) => (
            <li
              id={optionId(index())}
              class="viewer-select-option"
              role="option"
              aria-selected={option.value === props.value}
              data-active={index() === active()}
              onPointerEnter={() => setActive(index())}
              onClick={() => choose(index())}
            >
              {option.label}
            </li>
          )}
        </For>
      </ul>
    </div>
  );
}
