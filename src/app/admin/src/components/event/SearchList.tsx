import { For, Match, Show, Switch, createResource, createSignal } from 'solid-js';
import type { EventSearchSortBy, EventStatusValue, EventTypeValue, PerPage, SortOrder } from '../../generated';
import { client } from '../../utils/client';
import { normalizeDateValue } from '../../utils/date';
import { ListState } from '../ListState';
import { Pagination } from '../Pagination';
import { EVENT_STATUS_LABELS, EVENT_STATUS_OPTIONS, EVENT_TYPE_LABELS, EVENT_TYPE_OPTIONS } from './event-options';

const formatSchedule = (schedule: { startOn: string | null; endOn: string | null }): string => {
  if (!schedule.startOn) {
    return '未定';
  }
  const startOn = normalizeDateValue(schedule.startOn);
  const endOn = schedule.endOn ? normalizeDateValue(schedule.endOn) : null;
  return endOn && endOn !== startOn ? `${startOn}〜${endOn}` : startOn;
};

const PER_PAGE_OPTIONS: PerPage[] = [25, 50, 100];
const SORT_OPTIONS: Array<{ value: EventSearchSortBy; label: string }> = [
  { value: 'schedule', label: '開催時期' },
  { value: 'title', label: 'タイトル' },
];

type TypeFilter = '' | `${EventTypeValue}`;
type StatusFilter = '' | `${EventStatusValue}`;

type SearchParams = {
  title: string;
  type: TypeFilter;
  status: StatusFilter;
  sort: EventSearchSortBy;
  order: SortOrder;
  page: number;
  perPage: PerPage;
};

const DEFAULT_PARAMS: SearchParams = {
  title: '',
  type: '',
  status: '',
  sort: 'schedule',
  order: 'asc',
  page: 1,
  perPage: 25,
};

const pick = <T extends string | number>(value: unknown, candidates: readonly T[], fallback: T): T =>
  candidates.find(candidate => String(candidate) === value) ?? fallback;

const getInitialParams = (): SearchParams => {
  const query = new URLSearchParams(window.location.search);

  return {
    title: query.get('title') ?? '',
    type: pick(query.get('type'), EVENT_TYPE_OPTIONS.map(option => `${option.value}` as const), ''),
    status: pick(query.get('status'), EVENT_STATUS_OPTIONS.map(option => `${option.value}` as const), ''),
    sort: pick(query.get('sort'), SORT_OPTIONS.map(option => option.value), DEFAULT_PARAMS.sort),
    order: pick(query.get('order'), ['asc', 'desc'] as const, DEFAULT_PARAMS.order),
    page: Math.max(1, Number(query.get('page')) || 1),
    perPage: pick(query.get('per_page'), PER_PAGE_OPTIONS, DEFAULT_PARAMS.perPage),
  };
};

const updateUrl = (params: SearchParams) => {
  const query = new URLSearchParams();
  if (params.title) query.set('title', params.title);
  if (params.type) query.set('type', params.type);
  if (params.status) query.set('status', params.status);
  query.set('sort', params.sort);
  query.set('order', params.order);
  query.set('page', String(params.page));
  query.set('per_page', String(params.perPage));
  history.pushState(null, '', `?${query.toString()}`);
};

export const SearchList = () => {
  const initial = getInitialParams();
  // 検索ボタンを押すまで、入力中の条件は検索に反映しない
  const [params, setParams] = createSignal<SearchParams>(initial);
  const [input, setInput] = createSignal<SearchParams>(initial);
  const [fetchError, setFetchError] = createSignal<string | null>(null);

  const updateInput = (patch: Partial<SearchParams>) => setInput(current => ({ ...current, ...patch }));

  const apply = (next: SearchParams) => {
    setParams(next);
    updateUrl(next);
  };

  const [data, { refetch }] = createResource(params, async (current) => {
    setFetchError(null);
    const response = await client.api.events.search.get({
      query: {
        title: current.title || undefined,
        type: current.type,
        status: current.status,
        sort: current.sort,
        order: current.order,
        page: current.page,
        per_page: current.perPage,
      },
    });
    if (response.status === 401) {
      window.location.href = '/auth/login';
      return;
    }
    if (response.status === 403) {
      setFetchError('イベントの閲覧権限がありません');
      return;
    }
    if (!response.data) {
      setFetchError('データの取得に失敗しました');
      return;
    }
    return response.data;
  });

  const handleSearch = (event: Event) => {
    event.preventDefault();
    apply({ ...input(), page: 1 });
  };

  const handleReset = () => {
    setInput(DEFAULT_PARAMS);
    apply(DEFAULT_PARAMS);
  };

  const handlePageChange = (page: number) => {
    apply({ ...params(), page });
  };

  return (
    <>
      <form class="mb-4 flex flex-wrap items-end gap-4" onSubmit={handleSearch}>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-title">タイトル</label>
          <input
            id="event-title"
            class="input input-bordered input-sm"
            value={input().title}
            onInput={e => updateInput({ title: e.currentTarget.value })}
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-type">種別</label>
          <select
            id="event-type"
            class="select select-bordered select-sm"
            value={input().type}
            onChange={e => updateInput({ type: e.currentTarget.value as TypeFilter })}
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
            value={input().status}
            onChange={e => updateInput({ status: e.currentTarget.value as StatusFilter })}
          >
            <option value="">すべて</option>
            {EVENT_STATUS_OPTIONS.map(option => <option value={option.value}>{option.label}</option>)}
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-sort">ソート項目</label>
          <select
            id="event-sort"
            class="select select-bordered select-sm"
            value={input().sort}
            onChange={e => updateInput({ sort: e.currentTarget.value as EventSearchSortBy })}
          >
            {SORT_OPTIONS.map(option => <option value={option.value}>{option.label}</option>)}
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-order">並び順</label>
          <select
            id="event-order"
            class="select select-bordered select-sm"
            value={input().order}
            onChange={e => updateInput({ order: e.currentTarget.value as SortOrder })}
          >
            <option value="asc">昇順</option>
            <option value="desc">降順</option>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="event-per-page">表示件数</label>
          <select
            id="event-per-page"
            class="select select-bordered select-sm"
            value={input().perPage}
            onChange={e => updateInput({ perPage: Number(e.currentTarget.value) as PerPage })}
          >
            {PER_PAGE_OPTIONS.map(perPage => <option value={perPage}>{perPage}件</option>)}
          </select>
        </fieldset>
        <button class="btn btn-primary btn-sm mb-1" type="submit">検索</button>
        <button class="btn btn-ghost btn-sm mb-1" type="button" onClick={handleReset}>リセット</button>
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
                          <a
                            class="link link-hover"
                            href={`/events/${event.eventId}?back=${encodeURIComponent(window.location.search)}`}
                          >
                            {event.title}
                          </a>
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
      <Show when={!data.loading && !fetchError() && (data()?.maxPage ?? 0) > 1}>
        <Pagination page={params().page} maxPage={data()!.maxPage} onChange={handlePageChange} />
      </Show>
    </>
  );
};
