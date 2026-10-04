import { Match, Switch, createResource } from 'solid-js';
import type { ReleaseGetResponse } from '../../generated';
import { redirectToLogin } from '../../utils/auth-redirect';
import { client } from '../../utils/client';
import { createDirtyTracker, discardChanges } from '../../utils/dirty';
import { createFormErrors } from '../../utils/form-error';
import { createTabState } from '../../utils/tab';
import { createSubmitting } from '../../utils/use-submitting';
import { ActionMenu } from '../ActionMenu';
import { TargetHistory } from '../audit-log/TargetHistory';
import { EntityHeader } from '../EntityHeader';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { toMediumForms } from './MediaEditor';
import { createReleaseForm } from './release-form';
import { RELEASE_TABS, ReleaseFormFields, ReleaseTabList } from './ReleaseFormFields';

interface DetailViewProps {
  releaseId: string;
}

interface ReleaseFormProps {
  data: ReleaseGetResponse;
}

interface FetchOkState {
  status: 'ok';
  data: ReleaseGetResponse;
}

interface FetchErrorState {
  status: 'error';
}

type FetchState = FetchOkState | FetchErrorState;

const normalizeDateValue = (value: unknown): string => {
  if (value instanceof Date) {
    return Number.isNaN(value.getTime()) ? '' : value.toISOString().slice(0, 10);
  }

  if (typeof value !== 'string') {
    return '';
  }

  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    return value;
  }

  const parsed = new Date(value);

  return Number.isNaN(parsed.getTime()) ? '' : parsed.toISOString().slice(0, 10);
};

export const DetailView = (props: DetailViewProps) => {
  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api.releases({ releaseId: props.releaseId }).get();

    if (status === 401) {
      redirectToLogin();
      return { status: 'error' };
    }

    if (status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = '/release-groups';
      return { status: 'error' };
    }

    if (status === 422) {
      setFlash('不正なリクエストです', 'error');
      window.location.href = '/release-groups';
      return { status: 'error' };
    }

    if (!data) {
      return { status: 'error' };
    }

    return { status: 'ok', data };
  });

  const loadedData = () => {
    const state = resource();
    return state?.status === 'ok' ? state.data : undefined;
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
      <Match when={loadedData()}>{(data) => <ReleaseForm data={data()} />}</Match>
    </Switch>
  );
};

const ReleaseForm = (props: ReleaseFormProps) => {
  const groupUrl = `/release-groups/${props.data.release.releaseGroupId}`;
  const releaseId = props.data.release.releaseId;

  const form = createReleaseForm({
    name: props.data.release.name,
    releasedOn: normalizeDateValue(props.data.release.releasedOn),
    description: props.data.release.description,
    color: props.data.release.color,
    isDisplay: props.data.release.isDisplay,
    orderNo: props.data.release.orderNo,
    formatValues: props.data.release.formatValues,
    media: toMediumForms(props.data),
  });
  const { isDirty, allowLeave } = createDirtyTracker(form.toRequestBody);
  const { tab, setTab, bindForm } = createTabState(RELEASE_TABS, 'overview');

  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting: isUpdating, withSubmitting: withUpdating } = createSubmitting();
  const { isSubmitting: isDeleting, withSubmitting: withDeleting } = createSubmitting();

  const handleSubmit = withUpdating(async (e: SubmitEvent) => {
    e.preventDefault();
    clearErrors();

    if (!releaseId) {
      setFormError('更新対象のリリースIDを取得できませんでした');
      return;
    }

    const { data, error, status } = await client.api.releases({ releaseId }).put(form.toRequestBody());

    if (data) {
      setFlash('更新しました');
      allowLeave();
      window.location.href = groupUrl;
      return;
    }

    if (status === 404) {
      setFlash('データがありません', 'error');
      allowLeave();
      window.location.href = groupUrl;
      return;
    }

    handleError(status, error);
  });

  const handleDelete = withDeleting(async () => {
    if (!window.confirm('削除します。よろしいですか？')) {
      return;
    }

    clearErrors();

    if (!releaseId) {
      setFormError('削除対象のリリースIDを取得できませんでした');
      return;
    }

    const { error, status } = await client.api.releases({ releaseId }).delete();

    if (error) {
      handleError(status, error);
      return;
    }

    setFlash('削除しました');
    allowLeave();
    window.location.href = groupUrl;
  });

  return (
    <>
      <EntityHeader
        breadcrumb={{ href: groupUrl, label: props.data.releaseGroupTitle }}
        title={form.name() || '(版名なし)'}
        formId="release-form"
        isDirty={isDirty()}
        isSubmitting={isUpdating() || isDeleting()}
        submitLabel="保存"
        submittingLabel="保存中..."
        onDiscard={() => discardChanges(allowLeave)}
        tabs={<ReleaseTabList form={form} current={tab()} onChange={setTab} withHistory />}
        menu={
          <ActionMenu
            label="その他の操作"
            items={[
              {
                label: 'このリリースを削除する',
                danger: true,
                disabled: isUpdating() || isDeleting(),
                onSelect: handleDelete,
              },
            ]}
          />
        }
      />
      <FormError message={formError()} onClose={clearErrors} />
      <form ref={bindForm} id="release-form" onSubmit={handleSubmit}>
        <ReleaseFormFields
          form={form}
          tab={tab()}
          getFieldError={getFieldError}
          history={<TargetHistory targetType="Release" targetId={releaseId} active={tab() === 'history'} />}
        />
      </form>
    </>
  );
};
