import { createSignal } from 'solid-js';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { toMembersPayload, type MemberForm } from './member-form';
import { PersonGroupFields } from './PersonGroupFields';

export const CreateForm = () => {
  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const [name, setName] = createSignal('');
  const [members, setMembers] = createSignal<MemberForm[]>([]);

  const handleSubmit = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const { data, error, status } = await client.api['person-groups'].post({
      name: name(),
      members: toMembersPayload(members()),
    });

    if (data) {
      setFlash('作成しました');
      window.location.href = '/person-groups';
      return;
    }

    handleError(status, error);
  });

  return (
    <form onsubmit={handleSubmit}>
      <a href="/person-groups" class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-4xl space-y-6">
        <PersonGroupFields
          name={name()}
          onNameChange={setName}
          members={members()}
          onMembersChange={setMembers}
          nameError={getFieldError('name')}
        />
        <div class="flex justify-end">
          <button class="btn btn-primary" disabled={isSubmitting()}>
            {isSubmitting() ? '作成中...' : '作成'}
          </button>
        </div>
      </div>
    </form>
  );
};
