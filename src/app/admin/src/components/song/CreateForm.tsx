import { createResource, Match, Switch } from 'solid-js';
import type { Media, SongTag, SongType } from '../../generated';
import { redirectToLogin } from '../../utils/auth-redirect';
import { client } from '../../utils/client';
import { createDirtyTracker } from '../../utils/dirty';
import { createFormErrors } from '../../utils/form-error';
import { createTabState } from '../../utils/tab';
import { createSubmitting } from '../../utils/use-submitting';
import { EntityHeader } from '../EntityHeader';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { createSongForm } from './song-form';
import { SONG_TABS, SongFormFields, SongTabList } from './SongFormFields';

type CreateFormData = { types: SongType[]; tags: SongTag[]; media: Media[] };

interface CreateFormProps {
  data: CreateFormData;
}

interface FetchOkState {
  status: 'ok';
  data: CreateFormData;
}

interface FetchErrorState {
  status: 'error';
}

type FetchState = FetchOkState | FetchErrorState;

export const CreateView = () => {
  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api.songs['create-form'].get();

    if (status === 401) {
      redirectToLogin();
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
      <Match when={loadedData()}>{(data) => <CreateForm data={data()} />}</Match>
    </Switch>
  );
};

export const CreateForm = (props: CreateFormProps) => {
  const form = createSongForm({ media: props.data.media });
  const { isDirty, allowLeave } = createDirtyTracker(form.toCreateRequest);
  const { tab, setTab, bindForm } = createTabState(SONG_TABS, 'overview');
  const { formError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const handleSubmit = withSubmitting(async (e: SubmitEvent) => {
    e.preventDefault();
    clearErrors();

    const { data, error, status } = await client.api.songs.post(form.toCreateRequest());

    if (data) {
      setFlash('作成しました');
      allowLeave();
      window.location.href = '/songs';
      return;
    }

    handleError(status, error);
  });

  return (
    <>
      <EntityHeader
        breadcrumb={{ href: '/songs', label: '楽曲' }}
        title={form.title() || '新しい楽曲'}
        formId="song-form"
        isDirty={isDirty()}
        isSubmitting={isSubmitting()}
        submitLabel="作成"
        submittingLabel="作成中..."
        tabs={<SongTabList form={form} current={tab()} onChange={setTab} withHistory={false} />}
      />
      <FormError message={formError()} onClose={clearErrors} />
      <form ref={bindForm} id="song-form" onSubmit={handleSubmit}>
        <SongFormFields
          form={form}
          tab={tab()}
          types={props.data.types}
          availableTags={props.data.tags}
          getFieldError={getFieldError}
          withOrderNo={false}
        />
      </form>
    </>
  );
};
