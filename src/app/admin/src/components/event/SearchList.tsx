import { For, Match, Show, Switch, createResource, createSignal } from 'solid-js';
import type { EventSearchSortBy, EventStatusValue, EventTypeValue, SortOrder } from '../../generated';
import { client } from '../../utils/client';
import { normalizeDateValue } from '../../utils/date';
import { ListState } from '../ListState';

const typeName: Record<number, string> = { 1: 'ライブ', 2: '配信', 3: '個展', 99: 'その他' };
const statusName: Record<EventStatusValue, string> = { 1: '通常', 2: '延期', 3: '中止' };

const formatSchedule = (schedule: { startOn: string | null; endOn: string | null }): string => {
  if (!schedule.startOn) {
    return '未定';
  }
  const startOn = normalizeDateValue(schedule.startOn);
  return schedule.endOn ? `${startOn}〜${normalizeDateValue(schedule.endOn)}` : startOn;
};

export const SearchList = () => {
  const [title, setTitle] = createSignal('');
  const [type, setType] = createSignal('');
  const [status, setStatus] = createSignal('');
  const [fetchError, setFetchError] = createSignal<string | null>(null);
  const [data, { refetch }] = createResource(
    () => ({ title: title(), type: type(), status: status(), order: 'asc' as SortOrder }),
    async (params) => {
      setFetchError(null);
      const response = await client.api.events.search.get({
        query: {
          title: params.title || undefined,
          type: params.type ? Number(params.type) as EventTypeValue : undefined,
          status: params.status !== '' ? Number(params.status) as EventStatusValue : undefined,
          sort: 'schedule' as EventSearchSortBy,
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
            onChange={e => setType(e.currentTarget.value)}
          >
            <option value="">すべて</option>
            <option value="1">ライブ</option>
            <option value="2">配信</option>
            <option value="3">個展</option>
            <option value="99">その他</option>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-status">状態</label>
          <select
            id="event-status"
            class="select select-bordered select-sm"
            value={status()}
            onChange={e => setStatus(e.currentTarget.value)}
          >
            <option value="">すべて</option>
            <option value="1">通常</option>
            <option value="2">延期</option>
            <option value="3">中止</option>
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
                        <td>{typeName[event.typeValue] ?? 'その他'}</td>
                        <td>{formatSchedule(event.schedule)}</td>
                        <td>{statusName[event.statusValue]}</td>
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
