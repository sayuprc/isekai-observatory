import { createSignal, Show } from 'solid-js';
import type { ReleaseGroup, ReleaseGroupTypeValue } from '../../generated';
import { RELEASE_GROUP_TYPE_NAMES } from '../../generated/enum-names.gen';
import { toOptions } from '../../utils/enum-options';
import { FormRow } from '../FormRow';
import { SegmentedControl } from '../SegmentedControl';

const RELEASE_GROUP_TYPE_OPTIONS = toOptions(RELEASE_GROUP_TYPE_NAMES);

const DISPLAY_OPTIONS = [
  { value: 'true', label: '表示' },
  { value: 'false', label: '非表示' },
];

// 作成と編集で共通の入力状態
export const createReleaseGroupForm = (initial?: ReleaseGroup) => {
  const [title, setTitle] = createSignal(initial?.title ?? '');
  const [typeValue, setTypeValue] = createSignal<ReleaseGroupTypeValue>(initial?.type ?? 1);
  const [description, setDescription] = createSignal(initial?.description ?? '');
  const [isDisplay, setIsDisplay] = createSignal(initial?.isDisplay ?? true);
  const [orderNo, setOrderNo] = createSignal(initial?.orderNo ?? 1);

  const toRequestBody = () => ({
    title: title(),
    type: typeValue(),
    description: description(),
    isDisplay: isDisplay(),
    orderNo: orderNo(),
  });

  return {
    title,
    setTitle,
    typeValue,
    setTypeValue,
    description,
    setDescription,
    isDisplay,
    setIsDisplay,
    orderNo,
    setOrderNo,
    toRequestBody,
  };
};

export type ReleaseGroupFormState = ReturnType<typeof createReleaseGroupForm>;

interface ReleaseGroupFieldsProps {
  form: ReleaseGroupFormState;
  getFieldError: (field: string) => string | undefined;
}

export const ReleaseGroupFields = (props: ReleaseGroupFieldsProps) => (
  <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border px-6 py-3">
    <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
    <FormRow label="タイトル" for="title">
      <input
        id="title"
        type="text"
        class="input w-full"
        required
        value={props.form.title()}
        onInput={(e) => props.form.setTitle(e.currentTarget.value)}
        classList={{ 'input-error': !!props.getFieldError('title') }}
      />
      <Show when={props.getFieldError('title')}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
    </FormRow>
    <FormRow label="種別">
      <SegmentedControl
        label="種別"
        options={RELEASE_GROUP_TYPE_OPTIONS}
        value={props.form.typeValue()}
        onChange={props.form.setTypeValue}
      />
      <Show when={props.getFieldError('type')}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
    </FormRow>
    <FormRow label="説明" for="description">
      <textarea
        id="description"
        class="textarea min-h-32 w-full"
        value={props.form.description()}
        onInput={(e) => props.form.setDescription(e.currentTarget.value)}
        classList={{ 'textarea-error': !!props.getFieldError('description') }}
      />
      <Show when={props.getFieldError('description')}>
        {(message) => <p class="text-xs text-error">{message()}</p>}
      </Show>
    </FormRow>
    <FormRow label="表示順" for="orderNo">
      <input
        id="orderNo"
        type="number"
        min="1"
        step="1"
        class="input w-40"
        value={props.form.orderNo()}
        onInput={(e) => props.form.setOrderNo(Number(e.currentTarget.value))}
        classList={{ 'input-error': !!props.getFieldError('orderNo') }}
      />
      <Show when={props.getFieldError('orderNo')}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
    </FormRow>
    <FormRow label="公開">
      <SegmentedControl
        label="公開"
        options={DISPLAY_OPTIONS}
        value={String(props.form.isDisplay())}
        onChange={(value) => props.form.setIsDisplay(value === 'true')}
      />
      <Show when={props.getFieldError('isDisplay')}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
    </FormRow>
  </fieldset>
);
