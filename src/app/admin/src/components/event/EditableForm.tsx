import { Match, Switch, createResource, createSignal } from 'solid-js';
import type { Event, EventScheduleTypeValue, EventStatusValue, EventTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { createFormErrors } from '../../utils/form-error';
import { createSubmitting } from '../../utils/use-submitting';
import { setFlash } from '../Flash';
import { FormError } from '../FormError';

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

const SCHEDULE_TYPES: Array<{ value: EventScheduleTypeValue; label: string }> = [
  { value: 1, label: '開催時期未定' },
  { value: 2, label: '日付' },
  { value: 3, label: '期間' },
  { value: 4, label: '日時' },
];

const localDateTimeValue = (value: string | null): string => value?.replace(/([+-]\d\d:\d\d|Z)$/, '').slice(0, 16) ?? '';

const utcDateTimeValue = (value: FormDataEntryValue | null): string | null => {
  const raw = value?.toString() ?? '';
  return raw === '' ? null : new Date(raw).toISOString();
};

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
      <Match when={resource()?.status === 'forbidden'}><div class="alert alert-error">活動の閲覧権限がありません。</div></Match>
      <Match when={resource.error || resource()?.status === 'error'}><div class="flex flex-col items-start gap-3"><p class="text-error">データの取得に失敗しました。</p><button type="button" class="btn btn-outline btn-sm" onClick={() => refetch()}>再試行</button></div></Match>
      <Match when={loadedData()}>{data => <EditableForm data={data()} />}</Match>
    </Switch>
  );
};

const EditableForm = (props: EditableFormProps) => {
  const { formError, clearErrors, handleError } = createFormErrors();
  const { isSubmitting, withSubmitting } = createSubmitting();
  const event = props.data.event;
  const [scheduleType, setScheduleType] = createSignal<EventScheduleTypeValue>(event.schedule.type);

  const save = withSubmitting(async (submitEvent: globalThis.Event) => {
    submitEvent.preventDefault();
    clearErrors();
    const form = (submitEvent.target as HTMLButtonElement).form as HTMLFormElement;
    const formData = new FormData(form);
    const typeValue = Number(formData.get('typeValue') ?? event.typeValue) as EventTypeValue;
    const statusValue = formData.get('statusValue');
    const selectedScheduleType = Number(formData.get('scheduleType') ?? scheduleType()) as EventScheduleTypeValue;
    const schedule = selectedScheduleType === 2
      ? { type: selectedScheduleType, startDate: formData.get('startDate')?.toString() || null, endDate: null, startDateTime: null, endDateTime: null, timeZone: null }
      : selectedScheduleType === 3
        ? { type: selectedScheduleType, startDate: formData.get('startDate')?.toString() || null, endDate: formData.get('endDate')?.toString() || null, startDateTime: null, endDateTime: null, timeZone: null }
        : selectedScheduleType === 4
          ? { type: selectedScheduleType, startDate: null, endDate: null, startDateTime: utcDateTimeValue(formData.get('startDateTime')), endDateTime: utcDateTimeValue(formData.get('endDateTime')), timeZone: formData.get('timeZone')?.toString() || null }
          : { type: selectedScheduleType, startDate: null, endDate: null, startDateTime: null, endDateTime: null, timeZone: null };
    const { data, error, status } = await client.api.events({ eventId: event.eventId }).put({
      title: formData.get('title')?.toString() ?? '',
      description: formData.get('description')?.toString() || null,
      typeValue,
      schedule,
      statusValue: statusValue ? Number(statusValue) as EventStatusValue : null,
      postponedToEventId: event.postponedToEventId,
      isDisplay: formData.get('isDisplay') === 'true',
      venueIds: event.venues.map(venue => venue.venueId),
      mediaIds: event.media.map(media => media.mediaId),
      sources: event.sources,
      performances: event.performances,
      setlist: event.setlist,
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
        <form onSubmit={save}>
          <fieldset class="fieldset bg-base-200 border-base-300 rounded-box border p-6">
            <legend class="px-2 text-sm font-semibold text-base-content/70">基本情報</legend>
            <label class="label" for="title">タイトル</label>
            <input id="title" name="title" class="input w-full" required maxLength={255} value={event.title} />
            <label class="label mt-4" for="description">説明</label>
            <textarea id="description" name="description" class="textarea w-full" rows={4}>{event.description ?? ''}</textarea>
            <div class="grid gap-4 md:grid-cols-2">
              <div><label class="label" for="typeValue">種別</label><select id="typeValue" name="typeValue" class="select w-full">{EVENT_TYPES.map(option => <option value={option.value} selected={event.typeValue === option.value}>{option.label}</option>)}</select></div>
              <div><label class="label" for="scheduleType">開催時期</label><select id="scheduleType" name="scheduleType" class="select w-full" value={scheduleType()} onChange={e => setScheduleType(Number(e.currentTarget.value) as EventScheduleTypeValue)}>{SCHEDULE_TYPES.map(option => <option value={option.value}>{option.label}</option>)}</select></div>
            </div>
            {scheduleType() === 2 && <div><label class="label" for="startDate">開催日</label><input id="startDate" name="startDate" type="date" class="input w-full" value={event.schedule.startDate ?? ''} /></div>}
            {scheduleType() === 3 && <div class="grid gap-4 md:grid-cols-2"><div><label class="label" for="startDate">開始日</label><input id="startDate" name="startDate" type="date" class="input w-full" value={event.schedule.startDate ?? ''} /></div><div><label class="label" for="endDate">終了日</label><input id="endDate" name="endDate" type="date" class="input w-full" value={event.schedule.endDate ?? ''} /></div></div>}
            {scheduleType() === 4 && <div class="grid gap-4 md:grid-cols-3"><div><label class="label" for="startDateTime">開始日時</label><input id="startDateTime" name="startDateTime" type="datetime-local" class="input w-full" value={localDateTimeValue(event.schedule.startDateTime)} /></div><div><label class="label" for="endDateTime">終了日時</label><input id="endDateTime" name="endDateTime" type="datetime-local" class="input w-full" value={localDateTimeValue(event.schedule.endDateTime)} /></div><div><label class="label" for="timeZone">タイムゾーン</label><input id="timeZone" name="timeZone" class="input w-full" value={event.schedule.timeZone ?? 'Asia/Tokyo'} /></div></div>}
            <div class="grid gap-4 md:grid-cols-2">
              <div><label class="label" for="statusValue">状態</label><select id="statusValue" name="statusValue" class="select w-full"><option value="" selected={event.statusValue === null}>通常</option><option value="1" selected={event.statusValue === 1}>延期</option><option value="2" selected={event.statusValue === 2}>中止</option></select></div>
              <div><label class="label" for="isDisplay">表示設定</label><select id="isDisplay" name="isDisplay" class="select w-full"><option value="true" selected={event.isDisplay}>表示する</option><option value="false" selected={!event.isDisplay}>表示しない</option></select></div>
            </div>
            <div class="mt-6 flex justify-end"><button type="submit" class="btn btn-primary" disabled={isSubmitting()}>{isSubmitting() ? '更新中...' : '更新'}</button></div>
          </fieldset>
        </form>
        <fieldset class="rounded-box border border-error/20 bg-error/5 p-6">
          <legend class="px-2 text-sm font-semibold text-error">危険な操作</legend>
          <p class="mt-1 text-sm text-base-content/60">この操作は取り消せません。</p>
          <div class="mt-4"><button type="button" onClick={remove} class="btn btn-outline btn-error btn-sm" disabled={isSubmitting()}>この活動を削除する</button></div>
        </fieldset>
      </div>
    </>
  );
};
