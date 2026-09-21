import { For, Show } from 'solid-js';
import type { ReleaseFormatValue } from '../../generated';

export const RELEASE_FORMAT_OPTIONS: Array<{ value: ReleaseFormatValue; label: string }> = [
  { value: 1, label: '配信' },
  { value: 2, label: 'CD' },
  { value: 3, label: 'DVD' },
  { value: 4, label: 'Blu-ray' },
  { value: 99, label: 'その他' },
];

interface FormatCheckboxesProps {
  formatValues: ReleaseFormatValue[];
  onChange: (formatValues: ReleaseFormatValue[]) => void;
  fieldError?: string;
}

/** リリースの提供形態(複数選択)。CD と配信の同時発売などを 1 リリースで表す */
export const FormatCheckboxes = (props: FormatCheckboxesProps) => {
  const toggle = (value: ReleaseFormatValue, checked: boolean) => {
    const next = checked
      ? [...props.formatValues, value]
      : props.formatValues.filter(formatValue => formatValue !== value);
    props.onChange(next.toSorted((a, b) => a - b));
  };

  return (
    <div>
      <label class="label">提供形態</label>
      <div class="flex flex-wrap gap-4">
        <For each={RELEASE_FORMAT_OPTIONS}>
          {option => (
            <label class="label cursor-pointer gap-2">
              <input
                type="checkbox"
                class="checkbox checkbox-sm"
                checked={props.formatValues.includes(option.value)}
                onChange={e => toggle(option.value, e.currentTarget.checked)}
              />
              <span>{option.label}</span>
            </label>
          )}
        </For>
      </div>
      <Show when={props.fieldError}>
        {message => <p class="mt-1 text-xs text-error">{message()}</p>}
      </Show>
    </div>
  );
};
