import { For, Match, Show, Switch } from 'solid-js';
import type { PersonSearchSortBy, SortOrder } from '../../generated';
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

type Sort = PersonSearchSortBy;

type SearchParams = {
  name: string;
  sort: Sort;
  order: SortOrder;
  page: number;
  perPage: PerPage;
};

const DEFAULT_PARAMS: SearchParams = {
  name: '',
  sort: 'order_no',
  order: 'asc',
  page: 1,
  perPage: 25,
};

const parseParams = (query: URLSearchParams): SearchParams => ({
  name: query.get('name') ?? '',
  sort: pickParam(query.get('sort'), ['name', 'order_no'] as const, DEFAULT_PARAMS.sort),
  order: pickParam(query.get('order'), ['asc', 'desc'] as const, DEFAULT_PARAMS.order),
  page: parsePage(query.get('page')),
  perPage: pickParam(query.get('per_page'), PER_PAGE_OPTIONS, DEFAULT_PARAMS.perPage),
});

const toQuery = (params: SearchParams) => ({
  name: params.name,
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

  const { data, refetch, fetchError } = createSearchResource(params, (current) =>
    client.api.persons.search.get({
      query: {
        name: current.name,
        sort: current.sort,
        order: current.order,
        page: current.page,
        per_page: current.perPage,
      },
    }),
  );

  return (
    <>
      <form onSubmit={handleSearch} class="mb-4 flex flex-wrap items-end gap-4">
        <fieldset class="fieldset">
          <label class="fieldset-label" for="name">
            人物名
          </label>
          <input
            type="text"
            id="name"
            name="name"
            value={input().name}
            onInput={(e) => updateInput({ name: e.currentTarget.value })}
            class="input input-bordered input-sm"
            placeholder="人物名で検索"
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="sort">
            ソート項目
          </label>
          <select
            id="sort"
            name="sort"
            class="select select-bordered select-sm"
            onChange={(e) => updateInput({ sort: e.currentTarget.value as PersonSearchSortBy })}
          >
            <option value="order_no" selected={input().sort === 'order_no'}>
              表示順
            </option>
            <option value="name" selected={input().sort === 'name'}>
              人物名
            </option>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="order">
            並び順
          </label>
          <select
            id="order"
            name="order"
            class="select select-bordered select-sm"
            onChange={(e) => updateInput({ order: e.currentTarget.value as SortOrder })}
          >
            <option value="asc" selected={input().order === 'asc'}>
              昇順
            </option>
            <option value="desc" selected={input().order === 'desc'}>
              降順
            </option>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="perPage">
            表示件数
          </label>
          <select
            id="perPage"
            name="perPage"
            class="select select-bordered select-sm"
            onChange={(e) => updateInput({ perPage: Number(e.currentTarget.value) as PerPage })}
          >
            <For each={PER_PAGE_OPTIONS}>
              {(n) => (
                <option value={n} selected={input().perPage === n}>
                  {n}件
                </option>
              )}
            </For>
          </select>
        </fieldset>
        <button type="submit" class="btn btn-primary btn-sm mb-1">
          検索
        </button>
        <button type="button" class="btn btn-ghost btn-sm mb-1" onClick={handleReset}>
          リセット
        </button>
      </form>
      <div class="mb-4 flex justify-end">
        <a href="/persons/create" class="btn btn-primary btn-sm">
          新規作成
        </a>
      </div>
      <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
        <table class="table table-sm table-zebra md:table-md">
          <thead>
            <tr>
              <th>人物名</th>
              <th>表示順</th>
            </tr>
          </thead>
          <tbody>
            <Switch>
              <Match when={data.loading}>
                <ListState state="loading" colSpan={2} />
              </Match>
              <Match when={fetchError()}>
                {(message) => <ListState state="error" colSpan={2} message={message()} onRetry={() => refetch()} />}
              </Match>
              <Match when={data() && data()!.persons.length === 0}>
                <ListState state="empty" colSpan={2} />
              </Match>
              <Match when={data()}>
                {(result) => (
                  <For each={result().persons}>
                    {(person) => (
                      <tr class="transition-colors hover:bg-primary/30 focus-within:bg-primary/30">
                        <td>
                          <a
                            href={`/persons/${person.personId}?back=${encodeURIComponent(window.location.search)}`}
                            class="link link-hover font-medium"
                          >
                            {person.name}
                          </a>
                        </td>
                        <td>{person.orderNo}</td>
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
