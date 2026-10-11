import { createResource, createSignal, Match, Show, Switch } from 'solid-js';
import type { Media, MediaReferencedSong, MediaTypeValue } from '../../generated';
import { redirectToLogin } from '../../utils/auth-redirect';
import { client } from '../../utils/client';
import { normalizeDateTimeInputValue } from '../../utils/date';
import { createFormDirtyTracker, discardChanges } from '../../utils/dirty';
import { createFormErrors } from '../../utils/form-error';
import { getListUrl } from '../../utils/list-url';
import { createSubmitting } from '../../utils/use-submitting';
import { ActionMenu } from '../ActionMenu';
import { EntityHeader } from '../EntityHeader';
import { setFlash } from '../Flash';
import { FormColumns } from '../FormColumns';
import { FormError } from '../FormError';
import { MediaFields } from './MediaFields';

interface DetailViewProps {
  mediaId: string;
}

interface EditableFormProps {
  data: { media: Media; songs: MediaReferencedSong[] };
}

interface FetchOkState {
  status: 'ok';
  data: { media: Media; songs: MediaReferencedSong[] };
}

interface FetchErrorState {
  status: 'error';
}

type FetchState = FetchOkState | FetchErrorState;

export const DetailView = (props: DetailViewProps) => {
  const listUrl = getListUrl('/media');

  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api.media({ mediaId: props.mediaId }).get();

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

const EditableForm = (props: EditableFormProps) => {
  const listUrl = getListUrl('/media');

  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const [typeValue, setTypeValue] = createSignal<MediaTypeValue>(props.data.media.type);
  const [isDisplay, setIsDisplay] = createSignal(props.data.media.isDisplay);
  const { isDirty, allowLeave, bindForm } = createFormDirtyTracker(() => [typeValue(), isDisplay()]);

  const handleUpdate = withSubmitting(async (e: SubmitEvent) => {
    e.preventDefault();
    clearErrors();

    const formData = new FormData(e.currentTarget as HTMLFormElement);

    const mediaId = props.data.media.mediaId;

    if (!mediaId) {
      setFormError('更新対象のメディアIDを取得できませんでした');
      return;
    }

    const { data, error, status } = await client.api.media({ mediaId }).put({
      title: formData.get('title')?.toString() ?? '',
      url: formData.get('url')?.toString() ?? '',
      publishedAt: formData.get('publishedAt')?.toString() ?? '',
      type: Number(formData.get('type')) as MediaTypeValue,
      isDisplay: formData.get('isDisplay') === 'true',
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

    const mediaId = props.data.media.mediaId;

    if (!mediaId) {
      setFormError('削除対象のメディアIDを取得できませんでした');
      return;
    }

    const { error, status } = await client.api.media({ mediaId }).delete();

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
        breadcrumb={{ href: listUrl, label: 'メディア' }}
        title={props.data.media.title}
        formId="media-form"
        isDirty={isDirty()}
        isSubmitting={isSubmitting()}
        submitLabel="保存"
        submittingLabel="保存中..."
        onDiscard={() => discardChanges(allowLeave)}
        menu={
          <ActionMenu
            label="その他の操作"
            items={[
              { label: '元のページを開く', onSelect: () => window.open(props.data.media.url, '_blank', 'noreferrer') },
              { label: 'このメディアを削除する', danger: true, disabled: isSubmitting(), onSelect: handleDelete },
            ]}
          />
        }
      />
      <FormError message={formError()} onClose={clearErrors} />
      <FormColumns
        main={
          <form ref={bindForm} id="media-form" onSubmit={handleUpdate}>
            <MediaFields
              title={props.data.media.title}
              url={props.data.media.url}
              publishedAt={normalizeDateTimeInputValue(props.data.media.publishedAt)}
              typeValue={typeValue()}
              onTypeValueChange={setTypeValue}
              isDisplay={isDisplay()}
              onIsDisplayChange={setIsDisplay}
              getFieldError={getFieldError}
            />
          </form>
        }
        side={
          <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">参照中の楽曲</legend>
            <p class="mb-2 text-sm text-base-content/60">参照中の楽曲があるメディアは削除できません</p>
            <Show
              when={props.data.songs.length > 0}
              fallback={<p class="text-sm text-base-content/60">参照中の楽曲はありません。</p>}
            >
              <div class="overflow-x-auto">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>楽曲</th>
                      <th>楽曲順</th>
                      <th>メディア順</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    {props.data.songs.map((song) => (
                      <tr>
                        <td>{song.title}</td>
                        <td>{song.songOrderNo}</td>
                        <td>{song.mediaOrderNo}</td>
                        <td class="text-right">
                          <a href={`/songs/${song.songId}`} class="btn btn-ghost btn-xs">
                            楽曲を見る
                          </a>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </Show>
          </fieldset>
        }
      />
    </>
  );
};
