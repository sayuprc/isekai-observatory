import { createSignal } from 'solid-js';
import type { MediaTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { createFormDirtyTracker } from '../../utils/dirty';
import { createFormErrors } from '../../utils/form-error';
import { getListUrl } from '../../utils/list-url';
import { createSubmitting } from '../../utils/use-submitting';
import { EntityHeader } from '../EntityHeader';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { MediaFields } from './MediaFields';

export const CreateForm = () => {
  const listUrl = getListUrl('/media');
  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const [typeValue, setTypeValue] = createSignal<MediaTypeValue>(1);
  const [isDisplay, setIsDisplay] = createSignal(true);
  const { isDirty, allowLeave, bindForm } = createFormDirtyTracker(() => [typeValue(), isDisplay()]);

  const handleSubmit = withSubmitting(async (e: SubmitEvent) => {
    e.preventDefault();
    clearErrors();

    const form = e.currentTarget as HTMLFormElement;
    const formData = new FormData(form);

    const { data, error, status } = await client.api.media.post({
      title: formData.get('title')?.toString() ?? '',
      url: formData.get('url')?.toString() ?? '',
      publishedAt: formData.get('publishedAt')?.toString() ?? '',
      type: Number(formData.get('type')) as MediaTypeValue,
      isDisplay: formData.get('isDisplay') === 'true',
    });

    if (data) {
      setFlash('作成しました');
      allowLeave();
      window.location.href = listUrl;
      return;
    }

    handleError(status, error);
  });

  return (
    <>
      <EntityHeader
        breadcrumb={{ href: listUrl, label: 'メディア' }}
        title="新しいメディア"
        formId="media-form"
        isDirty={isDirty()}
        isSubmitting={isSubmitting()}
        submitLabel="作成"
        submittingLabel="作成中..."
      />
      <FormError message={formError()} onClose={clearErrors} />
      <form ref={bindForm} id="media-form" class="max-w-4xl" onSubmit={handleSubmit}>
        <MediaFields
          typeValue={typeValue()}
          onTypeValueChange={setTypeValue}
          isDisplay={isDisplay()}
          onIsDisplayChange={setIsDisplay}
          getFieldError={getFieldError}
        />
      </form>
    </>
  );
};
