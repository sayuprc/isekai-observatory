import { For, Match, Show, Switch, createResource, createSignal } from 'solid-js';
import type { EventStatusValue, EventTypeValue, SortOrder } from '../../generated';
import { client } from '../../utils/client';
import { normalizeDateValue } from '../../utils/date';
import { ListState } from '../ListState';
import { EVENT_STATUS_LABELS, EVENT_STATUS_OPTIONS, EVENT_TYPE_LABELS, EVENT_TYPE_OPTIONS } from './event-options';

const formatSchedule = (schedule: { startOn: string | null; endOn: string | null }): string => {
  if (!schedule.startOn) {
    return '未定';
  }
  const startOn = normalizeDateValue(schedule.startOn);
  const endOn = schedule.endOn ? normalizeDateValue(schedule.endOn) : null;
  return endOn && endOn !== startOn ? `${startOn}〜${endOn}` : startOn;
};

export const SearchList = () => {
  const [title, setTitle] = createSignal('');
  const [type, setType] = createSignal<'' | `${EventTypeValue}`>('');
  const [status, setStatus] = createSignal<'' | `${EventStatusValue}`>('');
  const [fetchError, setFetchError] = createSignal<string | null>(null);
  const [data, { refetch }] = createResource(
    () => ({ title: title(), type: type(), status: status(), order: 'asc' as SortOrder }),
    async (params) => {
      setFetchError(null);
      const response = await client.api.events.search.get({
        query: {
          title: params.title || undefined,
          type: params.type,
          status: params.status,
          sort: 'schedule',
          order: params.order,
          page: 1,
          per_page: 100,
        },
      });
      if (!response.data) {
        setFetchError('データの取得に失敗しました');
        return;
      }
      return response.data;
    },
  );

  return (
    <>
      <form
        class="mb-4 flex flex-wrap items-end gap-4"
        onSubmit={(event) => {
          event.preventDefault();
          refetch();
        }}
      >
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-title">タイトル</label>
          <input
            id="event-title"
            class="input input-bordered input-sm"
            value={title()}
            onInput={e => setTitle(e.currentTarget.value)}
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-type">種別</label>
          <select
            id="event-type"
            class="select select-bordered select-sm"
            value={type()}
            onChange={e => setType(e.currentTarget.value as '' | `${EventTypeValue}`)}
          >
            <option value="">すべて</option>
            {EVENT_TYPE_OPTIONS.map(option => <option value={option.value}>{option.label}</option>)}
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-status">状態</label>
          <select
            id="event-status"
            class="select select-bordered select-sm"
            value={status()}
            onChange={e => setStatus(e.currentTarget.value as '' | `${EventStatusValue}`)}
          >
            <option value="">すべて</option>
            {EVENT_STATUS_OPTIONS.map(option => <option value={option.value}>{option.label}</option>)}
          </select>
        </fieldset>
        <button class="btn btn-primary btn-sm" type="submit">検索</button>
      </form>
      <div class="mb-4 flex justify-end">
        <a href="/events/create" class="btn btn-primary btn-sm">新規作成</a>
      </div>
      <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
        <table class="table table-zebra">
          <thead>
            <tr>
              <th>タイトル</th>
              <th>種別</th>
              <th>開催時期</th>
              <th>状態</th>
            </tr>
          </thead>
          <tbody>
            <Switch>
              <Match when={data.loading}>
                <ListState state="loading" colSpan={4} />
              </Match>
              <Match when={fetchError()}>
                {message => <ListState state="error" colSpan={4} message={message()} onRetry={() => refetch()} />}
              </Match>
              <Match when={data() && data()!.events.length === 0}>
                <ListState state="empty" colSpan={4} />
              </Match>
              <Match when={data()}>
                {result => (
                  <For each={result().events}>
                    {event => (
                      <tr>
                        <td>
                          <a class="link link-hover" href={`/events/${event.eventId}`}>{event.title}</a>
                        </td>
                        <td>{EVENT_TYPE_LABELS[event.typeValue]}</td>
                        <td>{formatSchedule(event.schedule)}</td>
                        <td>{EVENT_STATUS_LABELS[event.statusValue]}</td>
                      </tr>
                    )}
                  </For>
                )}
              </Match>
            </Switch>
          </tbody>
        </table>
      </div>
      <Show when={data() && data()!.maxPage > 1}>
        <p class="mt-2 text-sm text-base-content/60">複数ページあります。検索条件を絞り込んでください。</p>
      </Show>
    </>
  );
};
