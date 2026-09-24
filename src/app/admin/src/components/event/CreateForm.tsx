import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { FormError } from '../FormError';
import { createEventForm } from './event-form';
import { EventFormFields } from './EventFormFields';

export const CreateForm = () => {
  const { formError, clearErrors, handleError, setFormError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const form = createEventForm();

  const submit = withSubmitting(async (event: SubmitEvent) => {
    event.preventDefault();
    clearErrors();
    const validationError = form.validate();
    if (validationError) {
      setFormError(validationError);
      return;
    }

    const { data, error, status } = await client.api.events.post(
      form.toRequestBody({ venueIds: [], mediaIds: [], sources: [] }),
    );
    if (data) {
      window.location.href = `/events/${data.event.eventId}`;
      return;
    }
    handleError(status, error);
  });

  return (
    <form class="max-w-4xl space-y-6" onSubmit={submit}>
      <FormError message={formError()} onClose={clearErrors} />
      <EventFormFields form={form} />
      <button class="btn btn-primary" disabled={isSubmitting()} type="submit">
        {isSubmitting() ? '保存中…' : '保存'}
      </button>
    </form>
  );
};
