import { For } from 'solid-js';

interface SegmentedControlProps<T extends string | number> {
  label: string;
  options: { value: T; label: string }[];
  value: T;
  onChange: (value: T) => void;
  disabled?: boolean;
}

// 選択肢が少ない単一選択。プルダウンと違い、選べる値と現在の値が常に見える
export const SegmentedControl = <T extends string | number>(props: SegmentedControlProps<T>) => (
  <div class="join flex-wrap" role="group" aria-label={props.label}>
    <For each={props.options}>
      {(option) => (
        <button
          type="button"
          class="btn join-item font-normal"
          classList={{ 'btn-primary font-semibold': option.value === props.value }}
          aria-pressed={option.value === props.value}
          disabled={props.disabled}
          onClick={() => props.onChange(option.value)}
        >
          {option.label}
        </button>
      )}
    </For>
  </div>
);
