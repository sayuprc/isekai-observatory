import { createSignal, For, Match, Show, Switch } from 'solid-js';
import type { EventSearchSortBy, EventStatusValue, EventSummary, EventTypeValue, SortOrder } from '../../generated';
import { client } from '../../utils/client';
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
import {
  allowsPerformances,
  allowsSetlist,
  EVENT_STATUS_OPTIONS,
  EVENT_TYPE_OPTIONS,
  formatSchedule,
} from './event-options';
import { EventPreview } from './EventPreview';

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

  // 右側のプレビューに出す行。ページを移っても残らないよう、取得結果の中から引く
  const [selectedId, setSelectedId] = createSignal<string | null>(null);
  const selectedEvent = () => data()?.events.find((event) => event.eventId === selectedId());
  const detailUrl = (event: EventSummary) =>
    `/events/${event.eventId}?back=${encodeURIComponent(window.location.search)}`;

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
      <div class="@container">
        <div class="grid gap-4 @5xl:grid-cols-[minmax(0,1fr)_20rem] @5xl:items-start">
          <div class="min-w-0 overflow-x-auto rounded-box border border-base-300 bg-base-100">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>タイトル</th>
                  <th>種別</th>
                  <th>開催時期</th>
                  <th>開催先</th>
                  <th class="text-right">披露</th>
                  <th class="text-right">セトリ</th>
                  <th class="text-right">出典</th>
                  <th>状態</th>
                  <th>公開</th>
                </tr>
              </thead>
              <tbody>
                <Switch>
                  <Match when={data.loading}>
                    <ListState state="loading" colSpan={9} />
                  </Match>
                  <Match when={fetchError()}>
                    {(message) => <ListState state="error" colSpan={9} message={message()} onRetry={() => refetch()} />}
                  </Match>
                  <Match when={data() && data()!.events.length === 0}>
                    <ListState state="empty" colSpan={9} />
                  </Match>
                  <Match when={data()}>
                    {(result) => (
                      <For each={result().events}>
                        {(event) => (
                          <tr
                            class="cursor-pointer hover:bg-base-200"
                            classList={{
                              'bg-info/10 shadow-[inset_3px_0_0_var(--color-info)]': event.eventId === selectedId(),
                            }}
                            onClick={() => setSelectedId(event.eventId)}
                            onFocusIn={() => setSelectedId(event.eventId)}
                          >
                            <td class="max-w-md">
                              <a class="link link-hover line-clamp-2" href={detailUrl(event)}>
                                {event.title}
                              </a>
                            </td>
                            <td class="whitespace-nowrap">{event.type.name}</td>
                            <td class="font-mono text-xs whitespace-nowrap">{formatSchedule(event.schedule)}</td>
                            <td class="max-w-48 truncate text-base-content/70">{event.venueNames.join(' · ')}</td>
                            <CountCell
                              count={event.performanceCount}
                              applicable={allowsPerformances(event.status.value)}
                            />
                            <CountCell
                              count={event.setlistItemCount}
                              applicable={allowsPerformances(event.status.value) && allowsSetlist(event.type.value)}
                            />
                            <CountCell count={event.sourceCount} applicable />
                            <td class="whitespace-nowrap">{event.status.name}</td>
                            <td>
                              <span
                                class={`badge badge-sm whitespace-nowrap ${event.isDisplay ? 'badge-success badge-soft' : 'badge-ghost'}`}
                              >
                                {event.isDisplay ? '公開' : '非公開'}
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
          <div class="hidden @5xl:sticky @5xl:top-20 @5xl:block">
            <EventPreview event={selectedEvent()} detailUrl={detailUrl} />
          </div>
        </div>
      </div>
      <Show when={!data.loading && !fetchError() && (data()?.maxPage ?? 0) > 1}>
        <Pagination page={params().page} maxPage={data()!.maxPage} onChange={handlePageChange} />
      </Show>
    </>
  );
};

// 件数のセル。0 件は未入力として目立たせ、種別や開催状態で持てない項目は「—」にする
const CountCell = (props: { count: number; applicable: boolean }) => (
  <td class="text-right font-mono text-xs">
    <Show when={props.applicable} fallback={<span class="text-base-content/40">—</span>}>
      <span classList={{ 'text-warning': props.count === 0 }}>{props.count}</span>
    </Show>
  </td>
);
