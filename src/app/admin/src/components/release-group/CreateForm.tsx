import { client } from '../../utils/client';
import { createDirtyTracker } from '../../utils/dirty';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { EntityHeader } from '../EntityHeader';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { createReleaseGroupForm, ReleaseGroupFields } from './ReleaseGroupFields';

export const CreateForm = () => {
  const form = createReleaseGroupForm();
  const { isDirty, allowLeave } = createDirtyTracker(form.toRequestBody);
  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const handleSubmit = withSubmitting(async (e: SubmitEvent) => {
    e.preventDefault();
    clearErrors();

    const { data, error, status } = await client.api['release-groups'].post(form.toRequestBody());

    if (data) {
      setFlash('作成しました');
      allowLeave();
      window.location.href = `/release-groups/${data.releaseGroup.releaseGroupId}`;
      return;
    }

    handleError(status, error);
  });

  return (
    <>
      <EntityHeader
        breadcrumb={{ href: '/release-groups', label: 'リリースグループ' }}
        title={form.title() || '新しいリリースグループ'}
        formId="release-group-form"
        isDirty={isDirty()}
        isSubmitting={isSubmitting()}
        submitLabel="作成"
        submittingLabel="作成中..."
      />
      <FormError message={formError()} onClose={clearErrors} />
      <form id="release-group-form" class="max-w-5xl" onSubmit={handleSubmit}>
        <ReleaseGroupFields form={form} getFieldError={getFieldError} />
      </form>
    </>
  );
};
