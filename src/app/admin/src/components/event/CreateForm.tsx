import { client } from '../../utils/client';
import { createDirtyTracker } from '../../utils/dirty';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { EntityHeader } from '../EntityHeader';
import { FormError } from '../FormError';
import { createEventForm } from './event-form';
import { EventFormFields } from './EventFormFields';
import { EventMeta } from './EventMeta';

export const CreateForm = () => {
  const { formError, clearErrors, handleError, setFormError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const form = createEventForm();
  const { isDirty, allowLeave } = createDirtyTracker(form.toRequestBody);

  const submit = withSubmitting(async (event: SubmitEvent) => {
    event.preventDefault();
    clearErrors();
    const validationError = form.validate();
    if (validationError) {
      setFormError(validationError);
      return;
    }

    const { data, error, status } = await client.api.events.post(form.toRequestBody());
    if (data) {
      allowLeave();
      window.location.href = `/events/${data.event.eventId}`;
      return;
    }
    handleError(status, error);
  });

  return (
    <>
      <EntityHeader
        breadcrumb={{ href: '/events', label: 'イベント' }}
        title={form.title() || '新しいイベント'}
        meta={<EventMeta form={form} />}
        formId="event-form"
        isDirty={isDirty()}
        isSubmitting={isSubmitting()}
        submitLabel="作成"
        submittingLabel="作成中..."
      />
      <FormError message={formError()} onClose={clearErrors} />
      <form id="event-form" class="max-w-4xl space-y-6" onSubmit={submit}>
        <EventFormFields form={form} />
      </form>
    </>
  );
};
