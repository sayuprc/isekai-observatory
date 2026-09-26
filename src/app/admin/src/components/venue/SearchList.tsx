import { For, Match, Show, Switch } from 'solid-js';
import type { SortOrder, VenueKindValue, VenueSearchSortBy } from '../../generated';
import { client } from '../../utils/client';
import {
  PER_PAGE_OPTIONS,
  createSearchResource,
  createSearchState,
  parsePage,
  pickParam,
} from '../../utils/search-list';
import type { PerPageOption as PerPage } from '../../utils/search-list';
import { ListState } from '../ListState';
import { Pagination } from '../Pagination';

type KindFilter = '' | `${VenueKindValue}`;
type SortBy = VenueSearchSortBy;

type SearchParams = {
  name: string;
  kind: KindFilter;
  sort: SortBy;
  order: SortOrder;
  page: number;
  perPage: PerPage;
};

const DEFAULT_PARAMS: SearchParams = {
  name: '',
  kind: '',
  sort: 'name',
  order: 'asc',
  page: 1,
  perPage: 25,
};

const parseParams = (query: URLSearchParams): SearchParams => ({
  name: query.get('name') ?? '',
  kind: pickParam(query.get('kind'), ['1', '2'] as const, ''),
  sort: pickParam(query.get('sort'), ['name'] as const, DEFAULT_PARAMS.sort),
  order: pickParam(query.get('order'), ['asc', 'desc'] as const, DEFAULT_PARAMS.order),
  page: parsePage(query.get('page')),
  perPage: pickParam(query.get('per_page'), PER_PAGE_OPTIONS, DEFAULT_PARAMS.perPage),
});

const toQuery = (params: SearchParams) => ({
  name: params.name,
  kind: params.kind,
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
    current =>
      client.api.venues.search.get({
        query: {
          name: current.name,
          kind: current.kind || undefined,
          sort: current.sort,
          order: current.order,
          page: current.page,
          per_page: current.perPage,
        },
      }),
    { forbiddenMessage: '開催先の閲覧権限がありません' },
  );

  return (
    <>
      <form onSubmit={handleSearch} class="mb-4 flex flex-wrap items-end gap-4">
        <fieldset class="fieldset">
          <label class="fieldset-label" for="name">開催先名</label>
          <input
            id="name"
            class="input input-bordered input-sm"
            value={input().name}
            onInput={e => updateInput({ name: e.currentTarget.value })}
            placeholder="開催先名で検索"
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="kind">種別</label>
          <select
            id="kind"
            class="select select-bordered select-sm"
            onChange={e => updateInput({ kind: e.currentTarget.value as KindFilter })}
          >
            <option value="" selected={input().kind === ''}>すべて</option>
            <option value="1" selected={input().kind === '1'}>現地</option>
            <option value="2" selected={input().kind === '2'}>オンライン</option>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="sort">ソート項目</label>
          <select
            id="sort"
            class="select select-bordered select-sm"
            onChange={e => updateInput({ sort: e.currentTarget.value as SortBy })}
          >
            <option value="name" selected={input().sort === 'name'}>開催先名</option>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="order">並び順</label>
          <select
            id="order"
            class="select select-bordered select-sm"
            onChange={e => updateInput({ order: e.currentTarget.value as SortOrder })}
          >
            <option value="asc" selected={input().order === 'asc'}>昇順</option>
            <option value="desc" selected={input().order === 'desc'}>降順</option>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="perPage">表示件数</label>
          <select
            id="perPage"
            class="select select-bordered select-sm"
            onChange={e => updateInput({ perPage: Number(e.currentTarget.value) as PerPage })}
          >
            <For each={PER_PAGE_OPTIONS}>{n => <option value={n} selected={input().perPage === n}>{n}件</option>}</For>
          </select>
        </fieldset>
        <button type="submit" class="btn btn-primary btn-sm mb-1">検索</button>
        <button type="button" class="btn btn-ghost btn-sm mb-1" onClick={handleReset}>リセット</button>
      </form>
      <div class="mb-4 flex justify-end">
        <a href="/venues/create" class="btn btn-primary btn-sm">新規作成</a>
      </div>
      <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
        <table class="table table-sm table-zebra md:table-md">
          <thead>
            <tr>
              <th>開催先名</th>
              <th>種別</th>
            </tr>
          </thead>
          <tbody>
            <Switch>
              <Match when={data.loading}>
                <ListState state="loading" colSpan={2} />
              </Match>
              <Match when={fetchError()}>
                {message => <ListState state="error" colSpan={2} message={message()} onRetry={() => refetch()} />}
              </Match>
              <Match when={data() && data()!.venues.length === 0}>
                <ListState state="empty" colSpan={2} />
              </Match>
              <Match when={data()}>
                {result => (
                  <For each={result().venues}>
                    {venue => (
                      <tr class="transition-colors hover:bg-primary/30 focus-within:bg-primary/30">
                        <td>
                          <a
                            class="link link-hover font-medium"
                            href={`/venues/${venue.venueId}?back=${encodeURIComponent(window.location.search)}`}
                          >
                            {venue.name}
                          </a>
                        </td>
                        <td>{venue.kind.name}</td>
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
