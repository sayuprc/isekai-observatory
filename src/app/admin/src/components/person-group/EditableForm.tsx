import { createResource, createSignal, Match, Switch } from 'solid-js';
import type { PersonGroup } from '../../generated';
import { redirectToLogin } from '../../utils/auth-redirect';
import { client } from '../../utils/client';
import { createDirtyTracker, discardChanges } from '../../utils/dirty';
import { createFormErrors } from '../../utils/form-error';
import { getListUrl } from '../../utils/list-url';
import { createSubmitting } from '../../utils/use-submitting';
import { ActionMenu } from '../ActionMenu';
import { EntityHeader } from '../EntityHeader';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { toMembersPayload, type MemberForm } from './member-form';
import { PersonGroupFields } from './PersonGroupFields';

interface DetailViewProps {
  personGroupId: string;
}

type FetchState = { status: 'ok'; personGroup: PersonGroup } | { status: 'error' };

export const DetailView = (props: DetailViewProps) => {
  const listUrl = getListUrl('/person-groups');

  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api['person-groups']({ personGroupId: props.personGroupId }).get();

    if (status === 401) {
      redirectToLogin();
      return { status: 'error' };
    }

    if (status === 404 || status === 422) {
      setFlash(status === 404 ? 'データがありません' : '不正なリクエストです', 'error');
      window.location.href = listUrl;
      return { status: 'error' };
    }

    if (!data) {
      return { status: 'error' };
    }

    return { status: 'ok', personGroup: data.personGroup };
  });

  const loadedGroup = () => {
    const state = resource();
    return state?.status === 'ok' ? state.personGroup : undefined;
  };

  return (
    <Switch>
      <Match when={resource.loading}>
        <div class="flex items-center justify-center gap-3 py-10 text-base-content/70" role="status" aria-live="polite">
          <span class="loading loading-spinner loading-md" aria-hidden="true" />
          <span>読み込み中...</span>
        </div>
      </Match>
      <Match when={resource.error || resource()?.status === 'error'}>
        <div class="flex flex-col items-start gap-3">
          <p class="text-error">データの取得に失敗しました。</p>
          <button type="button" class="btn btn-outline btn-sm" onClick={() => refetch()}>
            再試行
          </button>
        </div>
      </Match>
      <Match when={loadedGroup()}>{(personGroup) => <EditableForm personGroup={personGroup()} />}</Match>
    </Switch>
  );
};

const EditableForm = (props: { personGroup: PersonGroup }) => {
  const listUrl = getListUrl('/person-groups');
  const personGroupId = props.personGroup.personGroupId;

  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const [name, setName] = createSignal(props.personGroup.name);
  const [members, setMembers] = createSignal<MemberForm[]>(
    props.personGroup.members.map((member) => ({ personId: member.personId, name: member.name })),
  );
  const { isDirty, allowLeave } = createDirtyTracker(() => ({ name: name(), members: toMembersPayload(members()) }));

  const handleUpdate = withSubmitting(async (e: SubmitEvent) => {
    e.preventDefault();
    clearErrors();

    const { data, error, status } = await client.api['person-groups']({ personGroupId }).put({
      name: name(),
      members: toMembersPayload(members()),
    });

    if (data) {
      setFlash('更新しました');
      allowLeave();
      window.location.href = listUrl;
      return;
    }

    if (status === 404) {
      setFlash('データがありません', 'error');
      allowLeave();
      window.location.href = listUrl;
      return;
    }

    handleError(status, error);
  });

  const handleDelete = withSubmitting(async () => {
    if (!window.confirm('削除します。よろしいですか？')) {
      return;
    }

    clearErrors();

    const { error, status } = await client.api['person-groups']({ personGroupId }).delete();

    if (error) {
      handleError(status, error);
      return;
    }

    setFlash('削除しました');
    allowLeave();
    window.location.href = listUrl;
  });

  return (
    <>
      <EntityHeader
        breadcrumb={{ href: listUrl, label: '人物グループ' }}
        title={name() || '(グループ名未入力)'}
        formId="person-group-form"
        isDirty={isDirty()}
        isSubmitting={isSubmitting()}
        submitLabel="保存"
        submittingLabel="保存中..."
        onDiscard={() => discardChanges(allowLeave)}
        menu={
          <ActionMenu
            label="その他の操作"
            items={[
              { label: 'このグループを削除する', danger: true, disabled: isSubmitting(), onSelect: handleDelete },
            ]}
          />
        }
      />
      <FormError message={formError()} onClose={clearErrors} />
      <p class="mb-4 max-w-4xl text-sm text-base-content/60">楽曲披露で使われているグループは削除できません</p>
      <form id="person-group-form" class="max-w-4xl space-y-6" onSubmit={handleUpdate}>
        <PersonGroupFields
          name={name()}
          onNameChange={setName}
          members={members()}
          onMembersChange={setMembers}
          nameError={getFieldError('name')}
        />
      </form>
    </>
  );
};
