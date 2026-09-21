import { For, Show, createSignal } from 'solid-js';
import type { ReleaseGroupTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

const RELEASE_GROUP_TYPE_OPTIONS: Array<{ value: ReleaseGroupTypeValue; label: string }> = [
  { value: 1, label: 'シングル' },
  { value: 2, label: 'アルバム' },
  { value: 3, label: 'EP' },
  { value: 99, label: 'その他' },
];

export const CreateForm = () => {
  const [typeValue, setTypeValue] = createSignal<ReleaseGroupTypeValue>(1);
  const [isDisplay, setIsDisplay] = createSignal(true);
  const [orderNo, setOrderNo] = createSignal(1);

  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const handleSubmit = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { data, error, status } = await client.api['release-groups'].post({
      title: formData.get('title')?.toString() ?? '',
      typeValue: typeValue(),
      description: formData.get('description')?.toString() ?? '',
      isDisplay: isDisplay(),
      orderNo: orderNo(),
    });

    if (data) {
      setFlash('作成しました');
      window.location.href = `/release-groups/${data.releaseGroup.releaseGroupId}`;
      return;
    }

    handleError(status, error);
  });

  return (
    <form onSubmit={handleSubmit}>
      <a href="/release-groups" class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-5xl space-y-6">
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
          <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
          <div class="grid gap-5 md:grid-cols-2">
            <div>
              <label class="label">タイトル</label>
              <input
                type="text"
                class="input w-full"
                name="title"
                required
                classList={{ 'input-error': !!getFieldError('title') }}
              />
              <Show when={getFieldError('title')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>
            </div>

            <div>
              <label class="label">種別</label>
              <select
                class="select select-bordered w-full"
                name="typeValue"
                value={String(typeValue())}
                onChange={e => setTypeValue(Number(e.currentTarget.value) as ReleaseGroupTypeValue)}
              >
                <For each={RELEASE_GROUP_TYPE_OPTIONS}>
                  {option => <option value={option.value}>{option.label}</option>}
                </For>
              </select>
              <Show when={getFieldError('typeValue')}>
                {message => <p class="mt-1 text-xs text-error">{message()}</p>}
              </Show>
            </div>

            <div class="md:col-span-2">
              <label class="label">説明</label>
              <textarea
                class="textarea textarea-bordered min-h-32 w-full"
                name="description"
                classList={{ 'textarea-error': !!getFieldError('description') }}
              />
              <Show when={getFieldError('description')}>
                {message => <p class="mt-1 text-xs text-error">{message()}</p>}
              </Show>
            </div>

            <div class="md:col-span-2">
              <label class="label">表示設定</label>
              <select
                class="select select-bordered w-full"
                value={String(isDisplay())}
                onChange={e => setIsDisplay(e.currentTarget.value === 'true')}
                classList={{ 'select-error': !!getFieldError('isDisplay') }}
              >
                <option value="true">表示する</option>
                <option value="false">表示しない</option>
              </select>
              <Show when={getFieldError('isDisplay')}>
                {message => <p class="mt-1 text-xs text-error">{message()}</p>}
              </Show>
            </div>

            <div>
              <label class="label">表示補助番号</label>
              <input
                type="number"
                class="input w-full"
                name="orderNo"
                min="1"
                value={orderNo()}
                onChange={e => setOrderNo(Number(e.currentTarget.value))}
                classList={{ 'input-error': !!getFieldError('orderNo') }}
              />
              <Show when={getFieldError('orderNo')}>
                {message => <p class="mt-1 text-xs text-error">{message()}</p>}
              </Show>
            </div>
          </div>
        </fieldset>

        <div class="flex justify-end">
          <button class="btn btn-primary" disabled={isSubmitting()}>
            {isSubmitting() ? '作成中...' : '作成'}
          </button>
        </div>
      </div>
    </form>
  );
};
