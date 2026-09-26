import { Match, Show, Switch, createResource } from 'solid-js';
import type { Venue, VenueKindValue } from '../../generated';
import { validateVenueName } from '../../schemas/venue';
import { redirectToLogin } from '../../utils/auth-redirect';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { getListUrl } from '../../utils/list-url';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

interface DetailViewProps { venueId: string }
interface EditableFormProps { data: { venue: Venue } }
type FetchState = { status: 'ok'; data: { venue: Venue } } | { status: 'forbidden' } | { status: 'error' };
type Payload = { name: string; kind: VenueKindValue };

export const DetailView = (props: DetailViewProps) => {
  const listUrl = getListUrl('/venues');
  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api.venues({ venueId: props.venueId }).get();
    if (status === 401) {
      redirectToLogin();
      return { status: 'error' };
    }
    if (status === 403) return { status: 'forbidden' };
    if (status === 404 || status === 422) {
      setFlash(status === 404 ? 'データがありません' : '不正なリクエストです', 'error');
      window.location.href = listUrl;
      return { status: 'error' };
    }
    return data ? { status: 'ok', data } : { status: 'error' };
  });

  const loadedData = () => {
    const state = resource();
    return state?.status === 'ok' ? state.data : undefined;
  };

  return (
    <Switch>
      <Match when={resource.loading}>
        <div class="flex items-center justify-center gap-3 py-10" role="status">
          <span class="loading loading-spinner loading-md" />
          読み込み中...
        </div>
      </Match>
      <Match when={resource()?.status === 'forbidden'}>
        <div class="alert alert-error">開催先の閲覧権限がありません。</div>
      </Match>
      <Match when={resource.error || resource()?.status === 'error'}>
        <div class="flex flex-col items-start gap-3">
          <p class="text-error">データの取得に失敗しました。</p>
          <button type="button" class="btn btn-outline btn-sm" onClick={() => refetch()}>再試行</button>
        </div>
      </Match>
      <Match when={loadedData()}>{data => <EditableForm data={data()} />}</Match>
    </Switch>
  );
};

const EditableForm = (props: EditableFormProps) => {
  const listUrl = getListUrl('/venues');
  const { formError, setFormError, getFieldError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const venueId = props.data.venue.venueId;

  const save = async (payload: Payload) => {
    const { data, error, status } = await client.api.venues({ venueId }).put(payload);
    if (data) {
      setFlash('更新しました');
      window.location.href = listUrl;
      return;
    }
    if (status === 403) {
      setFormError('開催先の編集権限がありません');
      return;
    }
    if (status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = listUrl;
      return;
    }
    handleError(status, error);
  };

  const handleUpdate = withSubmitting(async (event: Event) => {
    event.preventDefault();
    clearErrors();
    const form = (event.target as HTMLButtonElement).form as HTMLFormElement;
    const formData = new FormData(form);
    const payload: Payload = {
      name: formData.get('name')?.toString() ?? '',
      kind: Number(formData.get('kind')?.toString() ?? '1') as VenueKindValue,
    };
    const nameError = validateVenueName(payload.name);
    if (nameError) {
      setFormError(nameError);
      return;
    }

    await save(payload);
  });

  const handleDelete = withSubmitting(async (event: Event) => {
    event.preventDefault();
    if (!window.confirm('削除します。よろしいですか？')) return;
    const { error, status } = await client.api.venues({ venueId }).delete();
    if (status === 403) {
      setFormError('開催先の編集権限がありません');
      return;
    }
    if (status === 404) {
      setFlash('データがありません', 'error');
      window.location.href = listUrl;
      return;
    }
    if (error) {
      handleError(status, error);
      return;
    }
    setFlash('削除しました');
    window.location.href = listUrl;
  });

  return (
    <>
      <a href={listUrl} class="btn btn-ghost btn-sm mb-4">← 一覧に戻る</a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-4xl space-y-6">
        <form onSubmit={event => event.preventDefault()}>
          <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
            <label class="label" for="name">開催先名</label>
            <input
              id="name"
              type="text"
              class="input w-full"
              name="name"
              required
              maxLength={255}
              value={props.data.venue.name}
              classList={{ 'input-error': !!getFieldError('name') }}
            />
            <Show when={getFieldError('name')}>{message => <p class="mt-1 text-xs text-error">{message()}</p>}</Show>
            <label class="label mt-4" for="kind">種別</label>
            <select id="kind" name="kind" class="select w-full" required>
              <option value="1" selected={props.data.venue.kind.value === 1}>現地</option>
              <option value="2" selected={props.data.venue.kind.value === 2}>オンライン</option>
            </select>
            <div class="mt-6 flex justify-end">
              <button type="button" onClick={handleUpdate} class="btn btn-primary" disabled={isSubmitting()}>
                {isSubmitting() ? '更新中...' : '更新'}
              </button>
            </div>
          </fieldset>
        </form>
        <fieldset class="rounded-box border border-error/20 bg-error/5 p-6">
          <legend class="px-2 text-sm font-semibold text-error">危険な操作</legend>
          <p class="mt-1 text-sm text-base-content/60">この操作は取り消せません。</p>
          <div class="mt-4">
            <button
              type="button"
              onClick={handleDelete}
              class="btn btn-outline btn-error btn-sm"
              disabled={isSubmitting()}
            >
              {isSubmitting() ? '削除中...' : 'この開催先を削除する'}
            </button>
          </div>
        </fieldset>
      </div>
    </>
  );
};
