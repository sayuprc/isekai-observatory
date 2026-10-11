import { createSignal } from 'solid-js';
import type { VenueKindValue } from '../../generated';
import { validateVenueName } from '../../schemas/venue';
import { client } from '../../utils/client';
import { createFormDirtyTracker } from '../../utils/dirty';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { EntityHeader } from '../EntityHeader';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { VenueFields } from './VenueFields';

type Payload = { name: string; kind: VenueKindValue };

export const CreateForm = () => {
  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const [kind, setKind] = createSignal<VenueKindValue>(1);
  const { isDirty, allowLeave, bindForm } = createFormDirtyTracker(kind);
  const save = async (payload: Payload) => {
    const { data, error, status } = await client.api.venues.post(payload);

    if (data) {
      setFlash('作成しました');
      allowLeave();
      window.location.href = '/venues';
      return;
    }

    if (status === 403) {
      setFormError('開催先の編集権限がありません');
      return;
    }

    handleError(status, error);
  };

  const handleSubmit = withSubmitting(async (event: SubmitEvent) => {
    event.preventDefault();
    clearErrors();
    const formData = new FormData(event.currentTarget as HTMLFormElement);
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
    <>
      <EntityHeader
        breadcrumb={{ href: '/venues', label: '開催先' }}
        title="新しい開催先"
        formId="venue-form"
        isDirty={isDirty()}
        isSubmitting={isSubmitting()}
        submitLabel="作成"
        submittingLabel="作成中..."
      />
      <FormError message={formError()} onClose={clearErrors} />
      <form ref={bindForm} id="venue-form" class="max-w-4xl" onSubmit={handleSubmit}>
        <VenueFields kind={kind()} onKindChange={setKind} nameError={getFieldError('name')} />
      </form>
    </>
  );
};
