import { For, Match, Show, Switch } from 'solid-js';
import type { EventSearchSortBy, EventStatusValue, EventTypeValue, SortOrder } from '../../generated';
import { client } from '../../utils/client';
import { normalizeDateValue } from '../../utils/date';
import {
  PER_PAGE_OPTIONS,
  createSearchResource,
  createSearchState,
  parsePage,
  pickParam,
} from '../../utils/search-list';
import type { PerPageOption } from '../../utils/search-list';
import { ListState } from '../ListState';
import { Pagination } from '../Pagination';
import { EVENT_STATUS_OPTIONS, EVENT_TYPE_OPTIONS } from './event-options';

const formatSchedule = (schedule: { startOn: string | null; endOn: string | null }): string => {
  if (!schedule.startOn) {
    return '未定';
  }
  const startOn = normalizeDateValue(schedule.startOn);
  const endOn = schedule.endOn ? normalizeDateValue(schedule.endOn) : null;
  return endOn && endOn !== startOn ? `${startOn}〜${endOn}` : startOn;
};

const SORT_OPTIONS: Array<{ value: EventSearchSortBy; label: string }> = [
  { value: 'schedule', label: '開催時期' },
  { value: 'title', label: 'タイトル' },
];

type TypeFilter = '' | `${EventTypeValue}`;
type StatusFilter = '' | `${EventStatusValue}`;
type DisplayFilter = '' | 'true' | 'false';

type SearchParams = {
  title: string;
  type: TypeFilter;
  status: StatusFilter;
  isDisplay: DisplayFilter;
  sort: EventSearchSortBy;
  order: SortOrder;
  page: number;
  perPage: PerPageOption;
};

const DEFAULT_PARAMS: SearchParams = {
  title: '',
  type: '',
  status: '',
  isDisplay: '',
  sort: 'schedule',
  order: 'asc',
  page: 1,
  perPage: 25,
};

const parseParams = (query: URLSearchParams): SearchParams => ({
  title: query.get('title') ?? '',
  type: pickParam(
    query.get('type'),
    EVENT_TYPE_OPTIONS.map((option) => `${option.value}` as const),
    '',
  ),
  status: pickParam(
    query.get('status'),
    EVENT_STATUS_OPTIONS.map((option) => `${option.value}` as const),
    '',
  ),
  isDisplay: pickParam(query.get('is_display'), ['true', 'false'] as const, ''),
  sort: pickParam(
    query.get('sort'),
    SORT_OPTIONS.map((option) => option.value),
    DEFAULT_PARAMS.sort,
  ),
  order: pickParam(query.get('order'), ['asc', 'desc'] as const, DEFAULT_PARAMS.order),
  page: parsePage(query.get('page')),
  perPage: pickParam(query.get('per_page'), PER_PAGE_OPTIONS, DEFAULT_PARAMS.perPage),
});

const toQuery = (params: SearchParams) => ({
  title: params.title,
  type: params.type,
  status: params.status,
  is_display: params.isDisplay,
  sort: params.sort,
  order: params.order,
  page: params.page,
  per_page: params.perPage,
});

export const SearchList = () => {
  const { params, input, updateInput, handleSearch, handleReset, handlePageChange } = createSearchState({
    defaults: DEFAULT_PARAMS,
    parse: parseParams,
    toQuery,
  });

  const { data, refetch, fetchError } = createSearchResource(
    params,
    (current) =>
      client.api.events.search.get({
        query: {
          title: current.title || undefined,
          type: current.type,
          status: current.status,
          is_display: current.isDisplay === '' ? undefined : current.isDisplay === 'true',
          sort: current.sort,
          order: current.order,
          page: current.page,
          per_page: current.perPage,
        },
      }),
    { forbiddenMessage: 'イベントの閲覧権限がありません' },
  );

  return (
    <>
      <form class="mb-4 flex flex-wrap items-end gap-4" onSubmit={handleSearch}>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-title">
            タイトル
          </label>
          <input
            id="event-title"
            class="input input-bordered input-sm"
            value={input().title}
            onInput={(e) => updateInput({ title: e.currentTarget.value })}
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-type">
            種別
          </label>
          <select
            id="event-type"
            class="select select-bordered select-sm"
            value={input().type}
            onChange={(e) => updateInput({ type: e.currentTarget.value as TypeFilter })}
          >
            <option value="">すべて</option>
            {EVENT_TYPE_OPTIONS.map((option) => (
              <option value={option.value}>{option.label}</option>
            ))}
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-status">
            状態
          </label>
          <select
            id="event-status"
            class="select select-bordered select-sm"
            value={input().status}
            onChange={(e) => updateInput({ status: e.currentTarget.value as StatusFilter })}
          >
            <option value="">すべて</option>
            {EVENT_STATUS_OPTIONS.map((option) => (
              <option value={option.value}>{option.label}</option>
            ))}
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-is-display">
            表示設定
          </label>
          <select
            id="event-is-display"
            class="select select-bordered select-sm"
            value={input().isDisplay}
            onChange={(e) => updateInput({ isDisplay: e.currentTarget.value as DisplayFilter })}
          >
            <option value="">すべて</option>
            <option value="true">表示する</option>
            <option value="false">表示しない</option>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-sort">
            ソート項目
          </label>
          <select
            id="event-sort"
            class="select select-bordered select-sm"
            value={input().sort}
            onChange={(e) => updateInput({ sort: e.currentTarget.value as EventSearchSortBy })}
          >
            {SORT_OPTIONS.map((option) => (
              <option value={option.value}>{option.label}</option>
            ))}
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-order">
            並び順
          </label>
          <select
            id="event-order"
            class="select select-bordered select-sm"
            value={input().order}
            onChange={(e) => updateInput({ order: e.currentTarget.value as SortOrder })}
          >
            <option value="asc">昇順</option>
            <option value="desc">降順</option>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-per-page">
            表示件数
          </label>
          <select
            id="event-per-page"
            class="select select-bordered select-sm"
            value={input().perPage}
            onChange={(e) => updateInput({ perPage: Number(e.currentTarget.value) as PerPageOption })}
          >
            {PER_PAGE_OPTIONS.map((perPage) => (
              <option value={perPage}>{perPage}件</option>
            ))}
          </select>
        </fieldset>
        <button class="btn btn-primary btn-sm mb-1" type="submit">
          検索
        </button>
        <button class="btn btn-ghost btn-sm mb-1" type="button" onClick={handleReset}>
          リセット
        </button>
      </form>
      <div class="mb-4 flex justify-end">
        <a href="/events/create" class="btn btn-primary btn-sm">
          新規作成
        </a>
      </div>
      <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
        <table class="table table-zebra">
          <thead>
            <tr>
              <th>タイトル</th>
              <th>種別</th>
              <th>開催時期</th>
              <th>状態</th>
              <th>表示設定</th>
            </tr>
          </thead>
          <tbody>
            <Switch>
              <Match when={data.loading}>
                <ListState state="loading" colSpan={5} />
              </Match>
              <Match when={fetchError()}>
                {(message) => <ListState state="error" colSpan={5} message={message()} onRetry={() => refetch()} />}
              </Match>
              <Match when={data() && data()!.events.length === 0}>
                <ListState state="empty" colSpan={5} />
              </Match>
              <Match when={data()}>
                {(result) => (
                  <For each={result().events}>
                    {(event) => (
                      <tr>
                        <td>
                          <a
                            class="link link-hover"
                            href={`/events/${event.eventId}?back=${encodeURIComponent(window.location.search)}`}
                          >
                            {event.title}
                          </a>
                        </td>
                        <td>{event.type.name}</td>
                        <td>{formatSchedule(event.schedule)}</td>
                        <td>{event.status.name}</td>
                        <td>
                          <span
                            class={`badge badge-sm ${event.isDisplay ? 'badge-success badge-soft' : 'badge-ghost'}`}
                          >
                            {event.isDisplay ? '表示する' : '表示しない'}
                          </span>
                        </td>
                      </tr>
                    )}
                  </For>
                )}
              </Match>
            </Switch>
          </tbody>
        </table>
      </div>
      <Show when={!data.loading && !fetchError() && (data()?.maxPage ?? 0) > 1}>
        <Pagination page={params().page} maxPage={data()!.maxPage} onChange={handlePageChange} />
      </Show>
    </>
  );
};
