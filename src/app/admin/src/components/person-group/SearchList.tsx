import { For, Match, Show, Switch } from 'solid-js';
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

type SearchParams = {
  name: string;
  page: number;
  perPage: PerPage;
};

const DEFAULT_PARAMS: SearchParams = {
  name: '',
  page: 1,
  perPage: 25,
};

const parseParams = (query: URLSearchParams): SearchParams => ({
  name: query.get('name') ?? '',
  page: parsePage(query.get('page')),
  perPage: pickParam(query.get('per_page'), PER_PAGE_OPTIONS, DEFAULT_PARAMS.perPage),
});

const toQuery = (params: SearchParams) => ({
  name: params.name,
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
    client.api['person-groups'].search.get({
      query: {
        name: current.name,
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
            グループ名
          </label>
          <input
            type="text"
            id="name"
            name="name"
            value={input().name}
            onInput={(e) => updateInput({ name: e.currentTarget.value })}
            class="input input-bordered input-sm"
            placeholder="グループ名で検索"
          />
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
        <a href="/person-groups/create" class="btn btn-primary btn-sm">
          新規作成
        </a>
      </div>
      <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
        <table class="table table-sm table-zebra md:table-md">
          <thead>
            <tr>
              <th>グループ名</th>
              <th>メンバー</th>
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
              <Match when={data() && data()!.personGroups.length === 0}>
                <ListState state="empty" colSpan={2} />
              </Match>
              <Match when={data()}>
                {(result) => (
                  <For each={result().personGroups}>
                    {(personGroup) => (
                      <tr class="transition-colors hover:bg-primary/30 focus-within:bg-primary/30">
                        <td>
                          <a
                            href={`/person-groups/${personGroup.personGroupId}?back=${encodeURIComponent(window.location.search)}`}
                            class="link link-hover font-medium"
                          >
                            {personGroup.name}
                          </a>
                        </td>
                        <td>{personGroup.members.map((member) => member.name).join(' / ')}</td>
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
