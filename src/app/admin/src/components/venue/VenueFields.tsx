import { Show } from 'solid-js';
import type { VenueKindValue } from '../../generated';
import { FormRow } from '../FormRow';
import { SegmentedControl } from '../SegmentedControl';

const VENUE_KIND_OPTIONS: { value: VenueKindValue; label: string }[] = [
  { value: 1, label: '現地' },
  { value: 2, label: 'オンライン' },
];

interface VenueFieldsProps {
  name?: string;
  kind: VenueKindValue;
  onKindChange: (kind: VenueKindValue) => void;
  nameError?: string;
}

// 作成と編集で共通の入力欄。種別は選択肢ボタンにし、送信時に読めるよう hidden の入力に写す
export const VenueFields = (props: VenueFieldsProps) => (
  <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border px-6 py-3">
    <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
    <FormRow label="開催先名" for="name">
      <input
        id="name"
        type="text"
        class="input w-full"
        name="name"
        required
        maxLength={255}
        value={props.name ?? ''}
        classList={{ 'input-error': !!props.nameError }}
      />
      <Show when={props.nameError}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
    </FormRow>
    <FormRow label="種別">
      <SegmentedControl label="種別" options={VENUE_KIND_OPTIONS} value={props.kind} onChange={props.onKindChange} />
      <input type="hidden" name="kind" value={props.kind} />
    </FormRow>
  </fieldset>
);
