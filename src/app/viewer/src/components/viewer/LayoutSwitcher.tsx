import { For, Match, Switch, createEffect, createSignal, onMount } from 'solid-js';

type Option = { value: string; label: string };

type Props = {
  name: string;
  options: Option[];
  initialValue: string;
};

export default function LayoutSwitcher(props: Props) {
  const storageKey = () => `viewer-layout-${props.name}`;
  const [value, setValue] = createSignal(props.initialValue);

  onMount(() => {
    const saved = localStorage.getItem(storageKey());
    if (saved) setValue(saved);
  });

  createEffect(() => {
    const v = value();
    const panels = document.querySelectorAll(`[data-layout-group="${props.name}"] [data-layout-panel]`);
    for (const panel of panels) {
      if (!(panel instanceof HTMLElement)) continue;
      panel.hidden = panel.dataset.layoutPanel !== v;
    }
    localStorage.setItem(storageKey(), v);
  });

  return (
    <div class="seg">
      <For each={props.options}>
        {option => (
          <button
            type="button"
            class={value() === option.value ? 'is-active' : undefined}
            title={option.label}
            aria-label={option.label}
            onClick={() => setValue(option.value)}
          >
            <Switch>
              <Match when={option.value === 'cinema'}>
                <svg viewBox="0 0 16 16" aria-hidden="true">
                  <rect x="2" y="3.5" width="12" height="9" />
                  <path d="M7 6.5 L10.5 8 L7 9.5 Z" class="seg-fill" />
                </svg>
              </Match>
              <Match when={option.value === 'mosaic'}>
                <svg viewBox="0 0 16 16" aria-hidden="true">
                  <rect x="2" y="2" width="7" height="7" />
                  <rect x="10" y="2" width="4" height="4" />
                  <rect x="10" y="7" width="4" height="2" />
                  <rect x="2" y="10" width="4" height="4" />
                  <rect x="7" y="10" width="7" height="4" />
                </svg>
              </Match>
              <Match when={option.value === 'list'}>
                <svg viewBox="0 0 16 16" aria-hidden="true">
                  <circle cx="3" cy="4" r=".7" class="seg-fill" />
                  <circle cx="3" cy="8" r=".7" class="seg-fill" />
                  <circle cx="3" cy="12" r=".7" class="seg-fill" />
                  <line x1="6" y1="4" x2="14" y2="4" />
                  <line x1="6" y1="8" x2="14" y2="8" />
                  <line x1="6" y1="12" x2="14" y2="12" />
                </svg>
              </Match>
            </Switch>
          </button>
        )}
      </For>
    </div>
  );
}
