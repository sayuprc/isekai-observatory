import { Match, Switch, createResource, createSignal } from 'solid-js';
import type { Event, EventStatusValue, EventTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { normalizeDateValue } from '../../utils/date';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';
import {
  allowsPerformances,
  allowsSetlist,
  toPerformanceForms,
  toPerformancesPayload,
  toSetlistItemForms,
  toSetlistPayload,
  validateSetlistItems,
  type PerformanceForm,
  type SetlistItemForm,
} from './performance-form';
import { PerformanceEditor } from './PerformanceEditor';
import { SetlistEditor } from './SetlistEditor';

interface DetailViewProps {
  eventId: string;
}

interface EditableFormProps {
  data: { event: Event };
}

type FetchState = { status: 'ok'; data: { event: Event } } | { status: 'forbidden' } | { status: 'error' };

const EVENT_TYPES: Array<{ value: EventTypeValue; label: string }> = [
  { value: 1, label: 'ライブ' },
  { value: 2, label: '配信' },
  { value: 3, label: '個展' },
  { value: 99, label: 'その他' },
];

const getListUrl = () => '/events';

export const DetailView = (props: DetailViewProps) => {
  const listUrl = getListUrl();
  const [resource, { refetch }] = createResource(async (): Promise<FetchState> => {
    const { data, status } = await client.api.events({ eventId: props.eventId }).get();
    if (status === 401) {
      window.location.href = '/auth/login';
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
      <Match when={resource.loading}><div class="flex items-center justify-center gap-3 py-10" role="status"><span class="loading loading-spinner loading-md" />読み込み中...</div></Match>
      <Match when={resource()?.status === 'forbidden'}><div class="alert alert-error">イベントの閲覧権限がありません。</div></Match>
      <Match when={resource.error || resource()?.status === 'error'}><div class="flex flex-col items-start gap-3"><p class="text-error">データの取得に失敗しました。</p><button type="button" class="btn btn-outline btn-sm" onClick={() => refetch()}>再試行</button></div></Match>
      <Match when={loadedData()}>{data => <EditableForm data={data()} />}</Match>
    </Switch>
  );
};

const EditableForm = (props: EditableFormProps) => {
  const { formError, clearErrors, handleError, setFormError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const event = props.data.event;
  const [typeValue, setTypeValue] = createSignal<EventTypeValue>(event.typeValue);
  const [statusValue, setStatusValue] = createSignal<EventStatusValue>(event.statusValue);
  const [performances, setPerformances] = createSignal<PerformanceForm[]>(toPerformanceForms(event.performances));
  const [setlist, setSetlist] = createSignal<SetlistItemForm[]>(toSetlistItemForms(event.setlist));

  const save = withSubmitting(async (submitEvent: globalThis.Event) => {
    submitEvent.preventDefault();
    clearErrors();
    const form = submitEvent.currentTarget as HTMLFormElement;
    const formData = new FormData(form);
    const nextType = Number(formData.get('typeValue') ?? typeValue()) as EventTypeValue;
    const nextStatus = Number(formData.get('statusValue') ?? statusValue()) as EventStatusValue;
    const nextPerformances = allowsPerformances(nextStatus) ? performances() : [];
    const nextSetlist = allowsPerformances(nextStatus) && allowsSetlist(nextType) ? setlist() : [];
    const setlistError = validateSetlistItems(nextSetlist);
    if (setlistError) {
      setFormError(setlistError);
      return;
    }

    const schedule = {
      startOn: formData.get('startOn')?.toString() || null,
      endOn: formData.get('endOn')?.toString() || null,
    };
    const { data, error, status } = await client.api.events({ eventId: event.eventId }).put({
      title: formData.get('title')?.toString() ?? '',
      description: formData.get('description')?.toString() ?? '',
      typeValue: nextType,
      schedule,
      statusValue: nextStatus,
      isDisplay: formData.get('isDisplay') === 'true',
      venueIds: event.venues.map(venue => venue.venueId),
      mediaIds: event.media.map(media => media.mediaId),
      sources: event.sources,
      performances: toPerformancesPayload(nextPerformances),
      setlist: toSetlistPayload(nextSetlist, nextPerformances),
    });
    if (data) {
      setFlash('更新しました');
      window.location.href = getListUrl();
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
    window.location.href = getListUrl();
  });

  return (
    <>
      <a href={getListUrl()} class="btn btn-ghost btn-sm mb-4">← 一覧に戻る</a>
      <FormError message={formError()} onClose={clearErrors} />
      <div class="max-w-4xl space-y-6">
        <form class="space-y-6" onSubmit={save}>
          <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
            <label class="label" for="title">タイトル</label>
            <input id="title" name="title" class="input w-full" required maxLength={255} value={event.title} />
            <label class="label mt-4" for="description">説明</label>
            <textarea id="description" name="description" class="textarea w-full" rows={4}>{event.description ?? ''}</textarea>
            <div class="grid gap-4 md:grid-cols-2">
              <div>
                <label class="label" for="typeValue">種別</label>
                <select
                  id="typeValue"
                  name="typeValue"
                  class="select w-full"
                  value={typeValue()}
                  onChange={(e) => {
                    const next = Number(e.currentTarget.value) as EventTypeValue;
                    setTypeValue(next);
                    if (!allowsSetlist(next)) {
                      setSetlist([]);
                    }
                  }}
                >
                  {EVENT_TYPES.map(option => <option value={option.value}>{option.label}</option>)}
                </select>
              </div>
              <div>
                <label class="label" for="statusValue">状態</label>
                <select
                  id="statusValue"
                  name="statusValue"
                  class="select w-full"
                  value={statusValue()}
                  onChange={(e) => {
                    const next = Number(e.currentTarget.value) as EventStatusValue;
                    setStatusValue(next);
                    if (!allowsPerformances(next)) {
                      setPerformances([]);
                      setSetlist([]);
                    }
                  }}
                >
                  <option value="1">通常</option>
                  <option value="2">延期</option>
                  <option value="3">中止</option>
                </select>
              </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
              <div><label class="label" for="startOn">開始日</label><input id="startOn" name="startOn" type="date" class="input w-full" value={normalizeDateValue(event.schedule.startOn)} /></div>
              <div><label class="label" for="endOn">終了日</label><input id="endOn" name="endOn" type="date" class="input w-full" value={normalizeDateValue(event.schedule.endOn)} /></div>
            </div>
            <p class="mt-2 text-sm text-base-content/60">両方空は日付未定、開始日のみは単日、両方指定は期間です</p>
            <div class="grid gap-4 md:grid-cols-2">
              <div><label class="label" for="isDisplay">表示設定</label><select id="isDisplay" name="isDisplay" class="select w-full"><option value="true" selected={event.isDisplay}>表示する</option><option value="false" selected={!event.isDisplay}>表示しない</option></select></div>
            </div>
          </fieldset>

          <PerformanceEditor
            performances={performances()}
            onChange={(updater) => {
              setPerformances((prev) => {
                const next = updater(prev);
                const ids = new Set(next.map(performance => performance.performanceId));
                setSetlist(items =>
                  items.map(item => ({
                    ...item,
                    performanceIds: item.performanceIds.filter(id => ids.has(id)),
                  })));
                return next;
              });
            }}
            disabled={!allowsPerformances(statusValue())}
          />
          <SetlistEditor
            setlist={setlist()}
            performances={performances()}
            onChange={updater => setSetlist(updater)}
            disabled={!allowsPerformances(statusValue())}
            hidden={!allowsSetlist(typeValue())}
          />

          <div class="flex justify-end">
            <button type="submit" class="btn btn-primary" disabled={isSubmitting()}>
              {isSubmitting() ? '更新中...' : '更新'}
            </button>
          </div>
        </form>
        <fieldset class="rounded-box border border-error/20 bg-error/5 p-6">
          <legend class="px-2 text-sm font-semibold text-error">危険な操作</legend>
          <p class="mt-1 text-sm text-base-content/60">この操作は取り消せません。</p>
          <div class="mt-4"><button type="button" onClick={remove} class="btn btn-outline btn-error btn-sm" disabled={isSubmitting()}>このイベントを削除する</button></div>
        </fieldset>
      </div>
    </>
  );
};
