import { createSignal, For, onCleanup, onMount } from 'solid-js';

export interface ActionMenuItem {
  label: string;
  onSelect: () => void;
  danger?: boolean;
  disabled?: boolean;
}

interface ActionMenuProps {
  label: string;
  items: ActionMenuItem[];
}

// 頻度の低い操作や削除などをまとめる「…」メニュー
export const ActionMenu = (props: ActionMenuProps) => {
  const [details, setDetails] = createSignal<HTMLDetailsElement>();

  const close = () => details()?.removeAttribute('open');

  const handleDocumentClick = (event: MouseEvent) => {
    const element = details();
    if (element && !element.contains(event.target as Node)) {
      close();
    }
  };

  const handleKeyDown = (event: KeyboardEvent) => {
    const element = details();
    if (event.key === 'Escape' && element?.open) {
      close();
      element.querySelector('summary')?.focus();
    }
  };

  // サーバーでの描画では onCleanup も走るので、document への登録と解除はどちらも onMount の中で行う
  onMount(() => {
    document.addEventListener('click', handleDocumentClick);
    details()?.addEventListener('keydown', handleKeyDown);
    onCleanup(() => {
      document.removeEventListener('click', handleDocumentClick);
      details()?.removeEventListener('keydown', handleKeyDown);
    });
  });

  return (
    <details ref={setDetails} class="dropdown dropdown-end max-sm:dropdown-top">
      <summary class="btn btn-square btn-sm" aria-label={props.label}>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <circle cx="5" cy="12" r="1.8" />
          <circle cx="12" cy="12" r="1.8" />
          <circle cx="19" cy="12" r="1.8" />
        </svg>
      </summary>
      <ul class="dropdown-content menu z-40 mt-1 w-56 rounded-box border border-base-300 bg-base-200 p-1 shadow-lg">
        <For each={props.items}>
          {(item) => (
            <li class={item.disabled ? 'menu-disabled' : ''}>
              <button
                type="button"
                classList={{ 'text-error': item.danger }}
                disabled={item.disabled}
                onClick={() => {
                  close();
                  item.onSelect();
                }}
              >
                {item.label}
              </button>
            </li>
          )}
        </For>
      </ul>
    </details>
  );
};
