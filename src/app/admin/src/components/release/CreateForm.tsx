import { Match, Show, Switch, createResource } from 'solid-js';
import { redirectToLogin } from '../../utils/auth-redirect';
import { client } from '../../utils/client';
import { createDirtyTracker } from '../../utils/dirty';
import { createFormErrors } from '../../utils/form-error';
import { createTabState } from '../../utils/tab';
import { createSubmitting } from '../../utils/use-submitting';
import { EntityHeader } from '../EntityHeader';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { toMediumForms } from './MediaEditor';
import { createReleaseForm, EMPTY_RELEASE_INITIAL_VALUES, type ReleaseInitialValues } from './release-form';
import { RELEASE_TABS, ReleaseFormFields, ReleaseTabList } from './ReleaseFormFields';

interface CreateParams {
  releaseGroupId: string;
  sourceReleaseId: string;
}

const getCreateParams = (): CreateParams => {
  if (typeof window === 'undefined') {
    return { releaseGroupId: '', sourceReleaseId: '' };
  }

  const params = new URLSearchParams(window.location.search);

  return {
    releaseGroupId: params.get('releaseGroupId') ?? '',
    sourceReleaseId: params.get('sourceReleaseId') ?? '',
  };
};

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

interface FetchOkState {
  status: 'ok';
  initialValues: ReleaseInitialValues;
}

interface FetchErrorState {
  status: 'error';
}

type FetchState = FetchOkState | FetchErrorState;

export const CreateForm = () => {
  const params = getCreateParams();

  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    if (params.sourceReleaseId === '') {
      return { status: 'ok', initialValues: EMPTY_RELEASE_INITIAL_VALUES };
    }

    const { data, status } = await client.api.releases({ releaseId: params.sourceReleaseId }).get();

    if (status === 401) {
      redirectToLogin();
      return { status: 'error' };
    }

    if (!data) {
      return { status: 'error' };
    }

    return {
      status: 'ok',
      initialValues: {
        name: data.release.name,
        releasedOn: normalizeDateValue(data.release.releasedOn),
        description: data.release.description,
        color: data.release.color,
        isDisplay: data.release.isDisplay,
        orderNo: data.release.orderNo,
        formatValues: [...data.release.formatValues],
        media: toMediumForms(data),
      },
    };
  });

  const loadedInitialValues = () => {
    const state = resource();
    return state?.status === 'ok' ? state.initialValues : undefined;
  };

  return (
    <Show
      when={params.releaseGroupId !== ''}
      fallback={
        <div class="flex flex-col items-start gap-3">
          <p class="text-error">リリースグループが指定されていません。グループ詳細から追加してください。</p>
          <a href="/release-groups" class="btn btn-outline btn-sm">
            リリースグループ一覧へ
          </a>
        </div>
      }
    >
      <Switch>
        <Match when={resource.loading}>
          <div
            class="flex items-center justify-center gap-3 py-10 text-base-content/70"
            role="status"
            aria-live="polite"
          >
            <span class="loading loading-spinner loading-md" aria-hidden="true" />
            <span>読み込み中...</span>
          </div>
        </Match>
        <Match when={resource.error || resource()?.status === 'error'}>
          <div class="flex flex-col items-start gap-3">
            <p class="text-error">コピー元リリースの取得に失敗しました。</p>
            <button type="button" class="btn btn-outline btn-sm" onClick={() => refetch()}>
              再試行
            </button>
          </div>
        </Match>
        <Match when={loadedInitialValues()}>
          {(initialValues) => (
            <ReleaseCreateForm releaseGroupId={params.releaseGroupId} initialValues={initialValues()} />
          )}
        </Match>
      </Switch>
    </Show>
  );
};

interface ReleaseCreateFormProps {
  releaseGroupId: string;
  initialValues: ReleaseInitialValues;
}

const ReleaseCreateForm = (props: ReleaseCreateFormProps) => {
  const form = createReleaseForm(props.initialValues);
  const { isDirty, allowLeave } = createDirtyTracker(form.toRequestBody);
  const { tab, setTab, bindForm } = createTabState(RELEASE_TABS, 'overview');
  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const groupUrl = `/release-groups/${props.releaseGroupId}`;

  const handleSubmit = withSubmitting(async (e: SubmitEvent) => {
    e.preventDefault();
    clearErrors();

    const { data, error, status } = await client.api.releases.post({
      releaseGroupId: props.releaseGroupId,
      ...form.toRequestBody(),
    });

    if (data) {
      setFlash('作成しました');
      allowLeave();
      window.location.href = groupUrl;
      return;
    }

    handleError(status, error);
  });

  return (
    <>
      <EntityHeader
        breadcrumb={{ href: groupUrl, label: 'リリースグループ' }}
        title={form.name() || '新しいリリース'}
        formId="release-form"
        isDirty={isDirty()}
        isSubmitting={isSubmitting()}
        submitLabel="作成"
        submittingLabel="作成中..."
        tabs={<ReleaseTabList form={form} current={tab()} onChange={setTab} withHistory={false} />}
      />
      <FormError message={formError()} onClose={clearErrors} />
      <form ref={bindForm} id="release-form" onSubmit={handleSubmit}>
        <ReleaseFormFields form={form} tab={tab()} getFieldError={getFieldError} />
      </form>
    </>
  );
};
