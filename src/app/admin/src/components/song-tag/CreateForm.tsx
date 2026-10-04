import { Show } from 'solid-js';
import { client } from '../../utils/client';
import { createFormDirtyTracker } from '../../utils/dirty';
import { createFormErrors } from '../../utils/form-error';
import { getListUrl } from '../../utils/list-url';
import { createSubmitting } from '../../utils/use-submitting';
import { EntityHeader } from '../EntityHeader';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { FormRow } from '../FormRow';

export const CreateForm = () => {
  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const { isDirty, allowLeave, bindForm } = createFormDirtyTracker();
  const listHref = getListUrl('/song-tags');

  const handleSubmit = withSubmitting(async (e: SubmitEvent) => {
    e.preventDefault();
    clearErrors();

    const formData = new FormData(e.currentTarget as HTMLFormElement);

    const { data, error, status } = await client.api['song-tags'].post({
      name: formData.get('name')?.toString() ?? '',
    });

    if (data) {
      setFlash('作成しました');
      allowLeave();
      window.location.href = listHref;
      return;
    }

    handleError(status, error);
  });

  return (
    <>
      <EntityHeader
        breadcrumb={{ href: listHref, label: '楽曲タグ' }}
        title="新しい楽曲タグ"
        formId="song-tag-form"
        isDirty={isDirty()}
        isSubmitting={isSubmitting()}
        submitLabel="作成"
        submittingLabel="作成中..."
      />
      <FormError message={formError()} onClose={clearErrors} />
      <form ref={bindForm} id="song-tag-form" class="max-w-4xl" onSubmit={handleSubmit}>
        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border px-6 py-3">
          <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
          <FormRow label="楽曲タグ名" for="name">
            <input
              id="name"
              type="text"
              class="input w-full"
              name="name"
              required
              classList={{ 'input-error': !!getFieldError('name') }}
            />
            <Show when={getFieldError('name')}>{(message) => <p class="text-xs text-error">{message()}</p>}</Show>
          </FormRow>
        </fieldset>
      </form>
    </>
  );
};
