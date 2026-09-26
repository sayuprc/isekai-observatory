import { For, Match, Show, Switch } from 'solid-js';
import type { ReleaseGroupSearchSortBy, ReleaseGroupTypeValue, SortOrder } from '../../generated';
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

type DisplayFilter = '' | 'true' | 'false';
type Sort = ReleaseGroupSearchSortBy;
type Order = SortOrder;

const RELEASE_GROUP_TYPE_OPTIONS: Array<{ value: '' | `${ReleaseGroupTypeValue}`; label: string }> = [
  { value: '', label: 'すべて' },
  { value: '1', label: 'シングル' },
  { value: '2', label: 'アルバム' },
  { value: '3', label: 'EP' },
  { value: '99', label: 'その他' },
];

const normalizeDateDisplayValue = (value: unknown): string => {
  if (value instanceof Date) {
    return Number.isNaN(value.getTime()) ? '' : value.toISOString().slice(0, 10);
  }

  if (typeof value !== 'string') {
    return '';
  }

  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    return value;
  }

  const parsed = new Date(value);

  return Number.isNaN(parsed.getTime()) ? '' : parsed.toISOString().slice(0, 10);
};

const buildDetailHref = (releaseGroupId: string): string => {
  const back = typeof window === 'undefined' ? '' : window.location.search;
  const path = `/release-groups/${releaseGroupId}`;
  return back ? `${path}?back=${encodeURIComponent(back)}` : path;
};

type SearchParams = {
  title: string;
  type: '' | `${ReleaseGroupTypeValue}`;
  isDisplay: DisplayFilter;
  sort: Sort;
  order: Order;
  page: number;
  perPage: PerPage;
};

const DEFAULT_PARAMS: SearchParams = {
  title: '',
  type: '',
  isDisplay: '',
  sort: 'first_released_on',
  order: 'desc',
  page: 1,
  perPage: 25,
};

const parseParams = (query: URLSearchParams): SearchParams => ({
  title: query.get('title') ?? '',
  type: pickParam(query.get('type'), RELEASE_GROUP_TYPE_OPTIONS.map(option => option.value), ''),
  isDisplay: pickParam(query.get('is_display'), ['true', 'false'] as const, ''),
  sort: pickParam(query.get('sort'), ['first_released_on', 'title'] as const, DEFAULT_PARAMS.sort),
  order: pickParam(query.get('order'), ['asc', 'desc'] as const, DEFAULT_PARAMS.order),
  page: parsePage(query.get('page')),
  perPage: pickParam(query.get('per_page'), PER_PAGE_OPTIONS, DEFAULT_PARAMS.perPage),
});

const toQuery = (params: SearchParams) => ({
  title: params.title,
  type: params.type,
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

  const { data, refetch, fetchError } = createSearchResource(params, current =>
    client.api['release-groups'].search.get({
      query: {
        title: current.title,
        type: current.type || undefined,
        is_display: current.isDisplay === '' ? undefined : current.isDisplay === 'true',
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
          <label class="fieldset-label" for="title">
            タイトル
          </label>
          <input
            type="text"
            id="title"
            name="title"
            value={input().title}
            onInput={e => updateInput({ title: e.currentTarget.value })}
            class="input input-bordered input-sm"
            placeholder="作品名で検索"
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="type">
            種別
          </label>
          <select
            id="type"
            name="type"
            class="select select-bordered select-sm"
            onChange={e => updateInput({ type: e.currentTarget.value as '' | `${ReleaseGroupTypeValue}` })}
          >
            <For each={RELEASE_GROUP_TYPE_OPTIONS}>
              {option => (
                <option value={option.value} selected={input().type === option.value}>
                  {option.label}
                </option>
              )}
            </For>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="isDisplay">
            表示設定
          </label>
          <select
            id="isDisplay"
            name="isDisplay"
            class="select select-bordered select-sm"
            onChange={e => updateInput({ isDisplay: e.currentTarget.value as DisplayFilter })}
          >
            <option value="" selected={input().isDisplay === ''}>
              すべて
            </option>
            <option value="true" selected={input().isDisplay === 'true'}>
              表示する
            </option>
            <option value="false" selected={input().isDisplay === 'false'}>
              表示しない
            </option>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="sort">
            ソート項目
          </label>
          <select
            id="sort"
            name="sort"
            class="select select-bordered select-sm"
            onChange={e => updateInput({ sort: e.currentTarget.value as Sort })}
          >
            <option value="first_released_on" selected={input().sort === 'first_released_on'}>
              初リリース日
            </option>
            <option value="title" selected={input().sort === 'title'}>
              タイトル
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
            onChange={e => updateInput({ order: e.currentTarget.value as Order })}
          >
            <option value="desc" selected={input().order === 'desc'}>
              降順
            </option>
            <option value="asc" selected={input().order === 'asc'}>
              昇順
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
            onChange={e => updateInput({ perPage: Number(e.currentTarget.value) as PerPage })}
          >
            <For each={PER_PAGE_OPTIONS}>
              {n => (
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
        <a href="/release-groups/create" class="btn btn-primary btn-sm">
          新規作成
        </a>
      </div>

      <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
        <table class="table table-sm table-zebra md:table-md">
          <thead>
            <tr>
              <th>タイトル</th>
              <th>種別</th>
              <th>初リリース日</th>
              <th>表示設定</th>
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
              <Match when={data() && data()!.releaseGroups.length === 0}>
                <ListState state="empty" colSpan={4} message="条件に一致するリリースグループはありません。" />
              </Match>
              <Match when={data()}>
                {result => (
                  <For each={result().releaseGroups}>
                    {releaseGroup => (
                      <tr class="transition-colors hover:bg-primary/30 focus-within:bg-primary/30">
                        <td class="min-w-56">
                          <a href={buildDetailHref(releaseGroup.releaseGroupId)} class="link link-hover font-medium">
                            {releaseGroup.title}
                          </a>
                        </td>
                        <td class="whitespace-nowrap">
                          {RELEASE_GROUP_TYPE_OPTIONS.find(
                            option => option.value === String(releaseGroup.typeValue),
                          )?.label ?? '不明'}
                        </td>
                        <td class="whitespace-nowrap text-sm">
                          {releaseGroup.firstReleasedOn
                            ? normalizeDateDisplayValue(releaseGroup.firstReleasedOn)
                            : '—'}
                        </td>
                        <td class="whitespace-nowrap">
                          <span
                            class={`badge badge-sm ${
                              releaseGroup.isDisplay ? 'badge-success badge-soft' : 'badge-ghost'
                            }`}
                          >
                            {releaseGroup.isDisplay ? '表示する' : '表示しない'}
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
