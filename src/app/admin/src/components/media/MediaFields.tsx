import { Show } from 'solid-js';
import type { MediaTypeValue } from '../../generated';
import { MEDIA_TYPE_NAMES, toOptions } from '../../utils/enum-names';
import { FormRow } from '../FormRow';
import { SegmentedControl } from '../SegmentedControl';

const MEDIA_TYPE_OPTIONS = toOptions(MEDIA_TYPE_NAMES);

const DISPLAY_OPTIONS = [
  { value: 'true', label: '表示' },
  { value: 'false', label: '非表示' },
];

interface MediaFieldsProps {
  title?: string;
  url?: string;
  publishedAt?: string;
  typeValue: MediaTypeValue;
  onTypeValueChange: (typeValue: MediaTypeValue) => void;
  isDisplay: boolean;
  onIsDisplayChange: (isDisplay: boolean) => void;
  getFieldError: (field: string) => string | undefined;
}

// 作成と編集で共通の入力欄。選択肢ボタンの値は、送信時に読めるよう hidden の入力に写す
export const MediaFields = (props: MediaFieldsProps) => (
  <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border px-6 py-3">
    <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
    <FormRow label="タイトル" for="title">
      <input
        id="title"
        type="text"
        class="input w-full"
        name="title"
        required
        value={props.title ?? ''}
        classList={{ 'input-error': !!props.getFieldError('title') }}
      />
      <Show when={props.getFieldError('title')}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
    </FormRow>
    <FormRow label="URL" for="url">
      <input
        id="url"
        type="url"
        class="input w-full"
        name="url"
        required
        value={props.url ?? ''}
        classList={{ 'input-error': !!props.getFieldError('url') }}
      />
      <Show when={props.getFieldError('url')}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
    </FormRow>
    <FormRow label="公開日" for="publishedAt">
      <input
        id="publishedAt"
        type="datetime-local"
        class="input w-64 font-mono"
        name="publishedAt"
        step="1"
        required
        value={props.publishedAt ?? ''}
        classList={{ 'input-error': !!props.getFieldError('publishedAt') }}
      />
      <Show when={props.getFieldError('publishedAt')}>
        {(message) => <p class="text-xs text-error">{message()}</p>}
      </Show>
    </FormRow>
    <FormRow label="種別">
      <SegmentedControl
        label="種別"
        options={MEDIA_TYPE_OPTIONS}
        value={props.typeValue}
        onChange={props.onTypeValueChange}
      />
      <input type="hidden" name="type" value={props.typeValue} />
      <Show when={props.getFieldError('type')}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
    </FormRow>
    <FormRow label="公開">
      <SegmentedControl
        label="公開"
        options={DISPLAY_OPTIONS}
        value={String(props.isDisplay)}
        onChange={(value) => props.onIsDisplayChange(value === 'true')}
      />
      <input type="hidden" name="isDisplay" value={String(props.isDisplay)} />
      <Show when={props.getFieldError('isDisplay')}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
    </FormRow>
  </fieldset>
);
