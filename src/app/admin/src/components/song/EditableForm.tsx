import { createResource, Match, Switch } from 'solid-js';
import type { Media, Song, SongTag, SongType } from '../../generated';
import { redirectToLogin } from '../../utils/auth-redirect';
import { client } from '../../utils/client';
import { createDirtyTracker, discardChanges } from '../../utils/dirty';
import { createFormErrors } from '../../utils/form-error';
import { getListUrl } from '../../utils/list-url';
import { createTabState } from '../../utils/tab';
import { createSubmitting } from '../../utils/use-submitting';
import { ActionMenu } from '../ActionMenu';
import { TargetHistory } from '../audit-log/TargetHistory';
import { EntityHeader } from '../EntityHeader';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { createSongForm } from './song-form';
import { SONG_TABS, SongFormFields, SongTabList } from './SongFormFields';

interface DetailViewProps {
  songId: string;
}

type EditableFormData = { song: Song; types: SongType[]; tags: SongTag[]; media: Media[] };

interface EditableFormProps {
  data: EditableFormData;
}

interface FetchOkState {
  status: 'ok';
  data: EditableFormData;
}

interface FetchErrorState {
  status: 'error';
}

type FetchState = FetchOkState | FetchErrorState;

export const DetailView = (props: DetailViewProps) => {
  const listUrl = getListUrl('/songs');

  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api.songs({ songId: props.songId })['edit-form'].get();

    if (status === 401) {
      redirectToLogin();
      return { status: 'error' };
    }

    if (status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = listUrl;
      return { status: 'error' };
    }

    if (status === 422) {
      setFlash('不正なリクエストです', 'error');
      window.location.href = listUrl;
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
      <Match when={loadedData()}>{(data) => <EditableForm data={data()} />}</Match>
    </Switch>
  );
};

export const EditableForm = (props: EditableFormProps) => {
  const listUrl = getListUrl('/songs');
  const songId = props.data.song.songId;
  const form = createSongForm({ song: props.data.song, media: props.data.media });
  const { isDirty, allowLeave } = createDirtyTracker(form.toUpdateRequest);
  const { tab, setTab, bindForm } = createTabState(SONG_TABS, 'overview');
  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const handleUpdate = withSubmitting(async (e: SubmitEvent) => {
    e.preventDefault();
    clearErrors();

    if (!songId) {
      setFormError('更新対象の楽曲IDを取得できませんでした');
      return;
    }

    const { data, error, status } = await client.api.songs({ songId }).put(form.toUpdateRequest());

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

    if (!songId) {
      setFormError('削除対象の楽曲IDを取得できませんでした');
      return;
    }

    const { error, status } = await client.api.songs({ songId }).delete();

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
        breadcrumb={{ href: listUrl, label: '楽曲' }}
        title={form.title() || '(楽曲名未入力)'}
        formId="song-form"
        isDirty={isDirty()}
        isSubmitting={isSubmitting()}
        submitLabel="保存"
        submittingLabel="保存中..."
        onDiscard={() => discardChanges(allowLeave)}
        tabs={<SongTabList form={form} current={tab()} onChange={setTab} withHistory />}
        menu={
          <ActionMenu
            label="その他の操作"
            items={[{ label: 'この楽曲を削除する', danger: true, disabled: isSubmitting(), onSelect: handleDelete }]}
          />
        }
      />
      <FormError message={formError()} onClose={clearErrors} />
      <form ref={bindForm} id="song-form" onSubmit={handleUpdate}>
        <SongFormFields
          form={form}
          tab={tab()}
          types={props.data.types}
          availableTags={props.data.tags}
          getFieldError={getFieldError}
          withOrderNo
          history={<TargetHistory targetType="Song" targetId={songId} active={tab() === 'history'} />}
        />
      </form>
    </>
  );
};
