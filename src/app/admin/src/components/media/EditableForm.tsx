import { createResource, Match, Show, Switch } from 'solid-js';
import type { Media, MediaReferencedSong, MediaTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { normalizeDateTimeInputValue } from '../../utils/date';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

const MEDIA_TYPE_OPTIONS: Array<{ value: MediaTypeValue; label: string }> = [
  { value: 1, label: 'MV' },
  { value: 2, label: '音源動画' },
  { value: 3, label: '配信' },
  { value: 4, label: 'ショート' },
  { value: 5, label: '投稿' },
  { value: 99, label: 'その他' },
];

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

const getListUrl = () => {
  const back = new URLSearchParams(window.location.search).get('back') ?? '';
  const listQuery = (() => {
    if (!back.startsWith('?')) return '';
    try {
      const query = new URLSearchParams(back.slice(1)).toString();
      return query ? `?${query}` : '';
    } catch {
      return '';
    }
  })();

  return `/media${listQuery}`;
};

export const DetailView = (props: DetailViewProps) => {
  const listUrl = getListUrl();

  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api.media({ mediaId: props.mediaId }).get();

    if (status === 401) {
      window.location.href = '/auth/login';
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
      <Match when={loadedData()}>{data => <EditableForm data={data()} />}</Match>
    </Switch>
  );
};

const EditableForm = (props: EditableFormProps) => {
  const listUrl = getListUrl();

  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();

  const handleSubmit = async (e: Event) => {
    e.preventDefault();
  };

  const handleUpdate = withSubmitting(async (e: Event) => {
    e.preventDefault();
    clearErrors();

    const form = (e.target as HTMLButtonElement).form as HTMLFormElement;
    const formData = new FormData(form);

    const mediaId = props.data.media.mediaId;

    if (!mediaId) {
      setFormError('更新対象のメディアIDを取得できませんでした');
      return;
    }

    const { data, error, status } = await client.api.media({ mediaId }).put({
      title: formData.get('title')?.toString() ?? '',
      url: formData.get('url')?.toString() ?? '',
      publishedAt: formData.get('publishedAt')?.toString() ?? '',
      typeValue: Number(formData.get('typeValue')) as MediaTypeValue,
      isDisplay: formData.get('isDisplay') === 'true',
    });

    if (data) {
      setFlash('更新しました');
      window.location.href = listUrl;
      return;
    }

    if (status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = listUrl;
      return;
    }

    handleError(status, error);
  });

  const handleDelete = withSubmitting(async (e: Event) => {
    e.preventDefault();

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
    window.location.href = listUrl;
  });

  return (
    <>
      <a href={listUrl} class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-4xl space-y-6">
        <form onsubmit={handleSubmit}>
          <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
            <label class="label">タイトル</label>
            <input
              type="text"
              class="input w-full"
              name="title"
              value={props.data.media.title}
              classList={{ 'input-error': !!getFieldError('title') }}
            />
            <Show when={getFieldError('title')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

            <label class="label">URL</label>
            <input
              type="url"
              class="input w-full"
              name="url"
              value={props.data.media.url}
              classList={{ 'input-error': !!getFieldError('url') }}
            />
            <Show when={getFieldError('url')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>

            <label class="label">公開日</label>
            <input
              type="datetime-local"
              class="input w-full"
              name="publishedAt"
              value={normalizeDateTimeInputValue(props.data.media.publishedAt)}
              step="1"
              required
              classList={{ 'input-error': !!getFieldError('publishedAt') }}
            />
            <Show when={getFieldError('publishedAt')}>
              {message => <p class="mt-1 text-xs text-error">{message()}</p>}
            </Show>

            <div class="grid gap-4 md:grid-cols-2">
              <div>
                <label class="label">種別</label>
                <select
                  class="select w-full"
                  name="typeValue"
                  value={props.data.media.type.value}
                  classList={{ 'select-error': !!getFieldError('typeValue') }}
                >
                  {MEDIA_TYPE_OPTIONS.map(option => (
                    <option value={option.value}>{option.label}</option>
                  ))}
                </select>
                <Show when={getFieldError('typeValue')}>
                  {message => <p class="mt-1 text-xs text-error">{message()}</p>}
                </Show>
              </div>
            </div>

            <label class="label">表示設定</label>
            <select
              class="select w-full"
              name="isDisplay"
              value={String(props.data.media.isDisplay)}
              classList={{ 'select-error': !!getFieldError('isDisplay') }}
            >
              <option value="true">表示する</option>
              <option value="false">表示しない</option>
            </select>
            <Show when={getFieldError('isDisplay')}>
              {message => <p class="mt-1 text-xs text-error">{message()}</p>}
            </Show>

            <div class="mt-6 flex justify-end">
              <button onClick={handleUpdate} class="btn btn-primary" disabled={isSubmitting()}>
                {isSubmitting() ? '更新中...' : '更新'}
              </button>
            </div>
          </fieldset>
        </form>

        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
          <legend class="px-2 text-sm font-semibold text-base-content/70">参照中の楽曲</legend>
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
                  {props.data.songs.map(song => (
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

        <fieldset class="rounded-box border border-error/20 bg-error/5 p-6">
          <legend class="px-2 text-sm font-semibold text-error">危険な操作</legend>
          <p class="mt-1 text-sm text-base-content/60">
            この操作は取り消せません。参照中の楽曲があるメディアは削除できません。
          </p>
          <div class="mt-4">
            <button onClick={handleDelete} class="btn btn-outline btn-error btn-sm" disabled={isSubmitting()}>
              {isSubmitting() ? '削除中...' : 'このメディアを削除する'}
            </button>
          </div>
        </fieldset>
      </div>
    </>
  );
};
