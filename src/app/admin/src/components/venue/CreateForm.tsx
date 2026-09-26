import { Show } from 'solid-js';
import type { VenueKindValue } from '../../generated';
import { validateVenueName } from '../../schemas/venue';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

type Payload = { name: string; kind: VenueKindValue };

export const CreateForm = () => {
  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const save = async (payload: Payload) => {
    const { data, error, status } = await client.api.venues.post(payload);

    if (data) {
      setFlash('作成しました');
      window.location.href = '/venues';
      return;
    }

    if (status === 403) {
      setFormError('開催先の編集権限がありません');
      return;
    }

    handleError(status, error);
  };

  const handleSubmit = withSubmitting(async (event: Event) => {
    event.preventDefault();
    clearErrors();
    const formData = new FormData(event.target as HTMLFormElement);
    const payload: Payload = {
      name: formData.get('name')?.toString() ?? '',
      kind: Number(formData.get('kind')?.toString() ?? '1') as VenueKindValue,
    };

    const nameError = validateVenueName(payload.name);
    if (nameError) {
      setFormError(nameError);
      return;
    }

    await save(payload);
  });

  return (
    <form onSubmit={handleSubmit}>
      <a href="/venues" class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-4xl space-y-6">
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
          <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
          <label class="label" for="name">
            開催先名
          </label>
          <input
            id="name"
            type="text"
            class="input w-full"
            name="name"
            required
            maxLength={255}
            classList={{ 'input-error': !!getFieldError('name') }}
          />
          <Show when={getFieldError('name')}>{(message) => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

          <label class="label mt-4" for="kind">
            種別
          </label>
          <select id="kind" name="kind" class="select w-full" required>
            <option value="1">現地</option>
            <option value="2">オンライン</option>
          </select>

          <div class="mt-6 flex justify-end">
            <button class="btn btn-primary" disabled={isSubmitting()}>
              {isSubmitting() ? '作成中...' : '作成'}
            </button>
          </div>
        </fieldset>
      </div>
    </form>
  );
};
