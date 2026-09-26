import { For, Match, Show, Switch } from 'solid-js';
import type { SortOrder } from '../../generated';
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

type Sort = 'name' | 'order_no';

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
  perPage: 50,
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
    client.api['song-tags'].search.get({
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
            楽曲タグ名
          </label>
          <input
            type="text"
            id="name"
            name="name"
            value={input().name}
            onInput={(e) => updateInput({ name: e.currentTarget.value })}
            class="input input-bordered input-sm"
            placeholder="楽曲タグ名で検索"
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
            onChange={(e) => updateInput({ sort: e.currentTarget.value as Sort })}
          >
            <option value="order_no" selected={input().sort === 'order_no'}>
              表示順
            </option>
            <option value="name" selected={input().sort === 'name'}>
              楽曲タグ名
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
        <a href={`/song-tags/create?back=${encodeURIComponent(window.location.search)}`} class="btn btn-primary btn-sm">
          新規作成
        </a>
      </div>
      <div class="rounded-box border border-base-300 bg-base-100 overflow-x-auto">
        <table class="table table-sm table-zebra md:table-md">
          <thead>
            <tr>
              <th>楽曲タグ名</th>
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
              <Match when={data() && data()!.tags.length === 0}>
                <ListState state="empty" colSpan={2} message="条件に一致する楽曲タグはありません。" />
              </Match>
              <Match when={data()}>
                {(result) => (
                  <For each={result().tags}>
                    {(tag) => (
                      <tr class="hover:bg-primary/30 focus-within:bg-primary/30 transition-colors">
                        <td>
                          <a
                            href={`/song-tags/${tag.songTagId}?back=${encodeURIComponent(window.location.search)}`}
                            class="link link-hover font-medium"
                          >
                            {tag.name}
                          </a>
                        </td>
                        <td>{tag.orderNo}</td>
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
