import { Match, Switch, createResource } from 'solid-js';
import type { Event } from '../../generated';
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
import { createEventForm } from './event-form';
import { EVENT_TABS, EventFormFields, EventTabList } from './EventFormFields';
import { EventMeta } from './EventMeta';

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
  const { isDirty, allowLeave } = createDirtyTracker(form.toRequestBody);
  const listUrl = getListUrl('/events');
  const { tab, setTab, bindForm } = createTabState(EVENT_TABS, 'overview');

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
      allowLeave();
      window.location.href = listUrl;
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
    allowLeave();
    window.location.href = listUrl;
  });

  return (
    <>
      <EntityHeader
        breadcrumb={{ href: listUrl, label: 'イベント' }}
        title={form.title() || '(タイトル未入力)'}
        meta={<EventMeta form={form} />}
        formId="event-form"
        isDirty={isDirty()}
        isSubmitting={isSubmitting()}
        submitLabel="保存"
        submittingLabel="保存中..."
        onDiscard={() => discardChanges(allowLeave)}
        tabs={<EventTabList form={form} current={tab()} onChange={setTab} withHistory />}
        menu={
          <ActionMenu
            label="その他の操作"
            items={[{ label: 'このイベントを削除する', danger: true, disabled: isSubmitting(), onSelect: remove }]}
          />
        }
      />
      <FormError message={formError()} onClose={clearErrors} />
      <form ref={bindForm} id="event-form" onSubmit={save}>
        <EventFormFields
          form={form}
          tab={tab()}
          history={<TargetHistory targetType="Event" targetId={event.eventId} active={tab() === 'history'} />}
        />
      </form>
    </>
  );
};
