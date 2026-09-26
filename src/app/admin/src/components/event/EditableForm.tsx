import { Match, Switch, createResource } from 'solid-js';
import type { Event } from '../../generated';
import { redirectToLogin } from '../../utils/auth-redirect';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { getListUrl } from '../../utils/list-url';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import { createEventForm } from './event-form';
import { EventFormFields } from './EventFormFields';

interface DetailViewProps {
  eventId: string;
}

interface EditableFormProps {
  data: { event: Event };
}

type FetchState = { status: 'ok'; data: { event: Event } } | { status: 'forbidden' } | { status: 'error' };

// 一覧から渡された検索条件を引き継ぐ。パスは固定し、クエリだけを採用する
export const DetailView = (props: DetailViewProps) => {
  const listUrl = getListUrl('/events');
  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api.events({ eventId: props.eventId }).get();
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
        <div class="alert alert-error">イベントの閲覧権限がありません。</div>
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
  const { formError, clearErrors, handleError, setFormError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const event = props.data.event;
  const form = createEventForm(event);

  const save = withSubmitting(async (submitEvent: SubmitEvent) => {
    submitEvent.preventDefault();
    clearErrors();
    const validationError = form.validate();
    if (validationError) {
      setFormError(validationError);
      return;
    }

    const { data, error, status } = await client.api.events({ eventId: event.eventId }).put(form.toRequestBody());
    if (data) {
      setFlash('更新しました');
      window.location.href = getListUrl('/events');
      return;
    }
    handleError(status, error);
  });

  const remove = withSubmitting(async () => {
    if (!window.confirm('削除します。よろしいですか？')) return;
    const { error, status } = await client.api.events({ eventId: event.eventId }).delete();
    if (error) {
      handleError(status, error);
      return;
    }
    setFlash('削除しました');
    window.location.href = getListUrl('/events');
  });

  return (
    <>
      <a href={getListUrl('/events')} class="btn btn-ghost btn-sm mb-4">
        ← 一覧に戻る
      </a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-4xl space-y-6">
        <form class="space-y-6" onSubmit={save}>
          <EventFormFields form={form} />
          <div class="flex justify-end">
            <button type="submit" class="btn btn-primary" disabled={isSubmitting()}>
              {isSubmitting() ? '更新中...' : '更新'}
            </button>
          </div>
        </form>
        <fieldset class="rounded-box border border-error/20 bg-error/5 p-6">
          <legend class="px-2 text-sm font-semibold text-error">危険な操作</legend>
          <p class="mt-1 text-sm text-base-content/60">この操作は取り消せません。</p>
          <div class="mt-4">
            <button type="button" onClick={remove} class="btn btn-outline btn-error btn-sm" disabled={isSubmitting()}>
              このイベントを削除する
            </button>
          </div>
        </fieldset>
      </div>
    </>
  );
};
