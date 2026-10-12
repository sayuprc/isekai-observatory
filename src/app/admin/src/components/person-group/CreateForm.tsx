import { createSignal } from 'solid-js';
import { client } from '../../utils/client';
import { createDirtyTracker } from '../../utils/dirty';
import { createFormErrors } from '../../utils/form-error';
import { preventImplicitSubmit } from '../../utils/keyboard';
import { createSubmitting } from '../../utils/use-submitting';
import { EntityHeader } from '../EntityHeader';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { toMembersPayload, type MemberForm } from './member-form';
import { PersonGroupFields } from './PersonGroupFields';

export const CreateForm = () => {
  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const [name, setName] = createSignal('');
  const [displayName, setDisplayName] = createSignal('');
  const [members, setMembers] = createSignal<MemberForm[]>([]);
  const { isDirty, allowLeave } = createDirtyTracker(() => ({
    name: name(),
    displayName: displayName(),
    members: toMembersPayload(members()),
  }));

  const handleSubmit = withSubmitting(async (e: SubmitEvent) => {
    e.preventDefault();
    clearErrors();

    const { data, error, status } = await client.api['person-groups'].post({
      name: name(),
      displayName: displayName(),
      members: toMembersPayload(members()),
    });

    if (data) {
      setFlash('作成しました');
      allowLeave();
      window.location.href = '/person-groups';
      return;
    }

    handleError(status, error);
  });

  return (
    <>
      <EntityHeader
        breadcrumb={{ href: '/person-groups', label: '人物グループ' }}
        title={name() || '新しい人物グループ'}
        formId="person-group-form"
        isDirty={isDirty()}
        isSubmitting={isSubmitting()}
        submitLabel="作成"
        submittingLabel="作成中..."
      />
      <FormError message={formError()} onClose={clearErrors} />
      <form
        id="person-group-form"
        onKeyDown={preventImplicitSubmit}
        class="max-w-4xl space-y-6"
        onSubmit={handleSubmit}
      >
        <PersonGroupFields
          name={name()}
          onNameChange={setName}
          displayName={displayName()}
          onDisplayNameChange={setDisplayName}
          members={members()}
          onMembersChange={setMembers}
          nameError={getFieldError('name')}
          displayNameError={getFieldError('displayName')}
        />
      </form>
    </>
  );
};
