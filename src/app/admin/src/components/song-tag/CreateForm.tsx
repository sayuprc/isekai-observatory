import { Show } from 'solid-js';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

export const CreateForm = () => {
  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const back = new URLSearchParams(window.location.search).get('back') ?? '';
  const listHref = `/song-tags${back}`;

  const handleSubmit = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = e.target as HTMLFormElement;
    const formData = new FormData(form);

    const { data, error, status } = await client.api['song-tags'].post({
      name: formData.get('name')?.toString() ?? '',
    });

    if (data) {
      setFlash('作成しました');
      window.location.href = listHref;
      return;
    }

    handleError(status, error);
  });

  return (
    <>
      <a href={listHref} class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <form onsubmit={handleSubmit}>
        <FormError message={formError()} onClose={clearErrors} />
        <div class="max-w-4xl space-y-6">
          <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
            <label class="label">楽曲タグ名</label>
            <input
              type="text"
              class="input w-full"
              name="name"
              required
              classList={{ 'input-error': !!getFieldError('name') }}
            />
            <Show when={getFieldError('name')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

            <div class="mt-6 flex justify-end">
              <button class="btn btn-primary" disabled={isSubmitting()}>
                {isSubmitting() ? '作成中...' : '作成'}
              </button>
            </div>
          </fieldset>
        </div>
      </form>
    </>
  );
};
