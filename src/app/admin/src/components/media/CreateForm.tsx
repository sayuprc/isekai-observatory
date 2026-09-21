import { Show } from 'solid-js';
import type { MediaTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

const MEDIA_TYPE_OPTIONS: Array<{ value: MediaTypeValue; label: string }> = [
  { value: 1, label: 'MV' },
  { value: 2, label: '音源動画' },
  { value: 3, label: '配信' },
  { value: 4, label: 'ショート' },
  { value: 5, label: '投稿' },
  { value: 99, label: 'その他' },
];

const getListUrl = () => {
  const back = new URLSearchParams(window.location.search).get('back') ?? '';

  if (!back.startsWith('?')) {
    return '/media';
  }

  try {
    const query = new URLSearchParams(back.slice(1)).toString();
    return query ? `/media?${query}` : '/media';
  } catch {
    return '/media';
  }
};

export const CreateForm = () => {
  const listUrl = getListUrl();
  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const handleSubmit = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = e.currentTarget as HTMLFormElement;
    const formData = new FormData(form);

    const { data, error, status } = await client.api.media.post({
      title: formData.get('title')?.toString() ?? '',
      url: formData.get('url')?.toString() ?? '',
      publishedAt: formData.get('publishedAt')?.toString() ?? '',
      typeValue: Number(formData.get('typeValue')) as MediaTypeValue,
      isDisplay: formData.get('isDisplay') === 'true',
    });

    if (data) {
      setFlash('作成しました');
      window.location.href = listUrl;
      return;
    }

    handleError(status, error);
  });

  return (
    <>
      <a href={listUrl} class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-4xl space-y-6">
        <form onSubmit={handleSubmit}>
          <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
            <label class="label">タイトル</label>
            <input
              type="text"
              class="input w-full"
              name="title"
              required
              classList={{ 'input-error': !!getFieldError('title') }}
            />
            <Show when={getFieldError('title')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

            <label class="label">URL</label>
            <input
              type="url"
              class="input w-full"
              name="url"
              required
              classList={{ 'input-error': !!getFieldError('url') }}
            />
            <Show when={getFieldError('url')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

            <label class="label">公開日</label>
            <input
              type="datetime-local"
              class="input w-full"
              name="publishedAt"
              step="1"
              required
              classList={{ 'input-error': !!getFieldError('publishedAt') }}
            />
            <Show when={getFieldError('publishedAt')}>
              {message => <p class="mt-1 text-xs text-error">{message()}</p>}
            </Show>

            <div class="grid gap-4 md:grid-cols-2">
              <div>
                <label class="label">種別</label>
                <select
                  class="select w-full"
                  name="typeValue"
                  required
                  classList={{ 'select-error': !!getFieldError('typeValue') }}
                >
                  {MEDIA_TYPE_OPTIONS.map(option => (
                    <option value={option.value}>{option.label}</option>
                  ))}
                </select>
                <Show when={getFieldError('typeValue')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>
            </div>

            <label class="label">表示設定</label>
            <select class="select w-full" name="isDisplay" classList={{ 'select-error': !!getFieldError('isDisplay') }}>
              <option value="true">表示する</option>
              <option value="false">表示しない</option>
            </select>
            <Show when={getFieldError('isDisplay')}>
              {message => <p class="mt-1 text-xs text-error">{message()}</p>}
            </Show>

            <div class="mt-6 flex justify-end">
              <button class="btn btn-primary" disabled={isSubmitting()}>
                {isSubmitting() ? '作成中...' : '作成'}
              </button>
            </div>
          </fieldset>
        </form>
      </div>
    </>
  );
};
