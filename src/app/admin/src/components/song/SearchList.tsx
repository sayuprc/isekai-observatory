import { For, Match, Show, Switch } from 'solid-js';
import type { SongSearchTypeValue, SongTypeValue } from '../../generated';
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

const SONG_TYPE_BADGE_CLASS: Record<SongTypeValue, string> = {
  1: 'badge-warning',
  2: 'badge-info',
};

type Sort = 'title' | 'order_no';
type Order = 'asc' | 'desc';

type SearchParams = {
  title: string;
  type?: SongSearchTypeValue;
  isDisplay?: boolean;
  sort: Sort;
  order: Order;
  page: number;
  perPage: PerPage;
};

const DEFAULT_PARAMS: SearchParams = {
  title: '',
  type: undefined,
  isDisplay: undefined,
  sort: 'order_no',
  order: 'asc',
  page: 1,
  perPage: 25,
};

const parseParams = (query: URLSearchParams): SearchParams => {
  const isDisplay = query.get('is_display');

  return {
    title: query.get('title') ?? '',
    type: pickParam<SongSearchTypeValue | ''>(query.get('type'), ['1', '2'], '') || undefined,
    isDisplay: isDisplay === 'true' ? true : isDisplay === 'false' ? false : undefined,
    sort: pickParam(query.get('sort'), ['title', 'order_no'] as const, DEFAULT_PARAMS.sort),
    order: pickParam(query.get('order'), ['asc', 'desc'] as const, DEFAULT_PARAMS.order),
    page: parsePage(query.get('page')),
    perPage: pickParam(query.get('per_page'), PER_PAGE_OPTIONS, DEFAULT_PARAMS.perPage),
  };
};

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

  const { data, refetch, fetchError } = createSearchResource(params, (current) =>
    client.api.songs.search.get({
      query: {
        title: current.title,
        type: current.type,
        is_display: current.isDisplay,
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
            楽曲名
          </label>
          <input
            type="text"
            id="title"
            name="title"
            value={input().title}
            onInput={(e) => updateInput({ title: e.currentTarget.value })}
            class="input input-bordered input-sm"
            placeholder="楽曲名で検索"
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="type">
            楽曲種別
          </label>
          <select
            id="type"
            name="type"
            class="select select-bordered select-sm"
            onChange={(e) =>
              updateInput({
                type: e.currentTarget.value !== '' ? (e.currentTarget.value as SongSearchTypeValue) : undefined,
              })
            }
          >
            <option value="" selected={input().type === undefined}>
              すべて
            </option>
            <For each={data()?.types ?? []}>
              {(t) => (
                <option value={String(t.value)} selected={input().type === String(t.value)}>
                  {t.name}
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
            onChange={(e) =>
              updateInput({ isDisplay: e.currentTarget.value === '' ? undefined : e.currentTarget.value === 'true' })
            }
          >
            <option value="" selected={input().isDisplay === undefined}>
              すべて
            </option>
            <option value="true" selected={input().isDisplay === true}>
              表示する
            </option>
            <option value="false" selected={input().isDisplay === false}>
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
            onChange={(e) => updateInput({ sort: e.currentTarget.value as Sort })}
          >
            <option value="order_no" selected={input().sort === 'order_no'}>
              表示順
            </option>
            <option value="title" selected={input().sort === 'title'}>
              楽曲名
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
            onChange={(e) => updateInput({ order: e.currentTarget.value as Order })}
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
        <a href="/songs/create" class="btn btn-primary btn-sm">
          新規作成
        </a>
      </div>
      <div class="rounded-box border border-base-300 bg-base-100 overflow-x-auto">
        <table class="table table-sm table-zebra md:table-md">
          <thead>
            <tr>
              <th>楽曲名</th>
              <th>楽曲種別</th>
              <th>表示設定</th>
              <th>表示順</th>
            </tr>
          </thead>
          <tbody>
            <Switch>
              <Match when={data.loading}>
                <ListState state="loading" colSpan={4} />
              </Match>
              <Match when={fetchError()}>
                {(message) => <ListState state="error" colSpan={4} message={message()} onRetry={() => refetch()} />}
              </Match>
              <Match when={data() && data()!.songs.length === 0}>
                <ListState state="empty" colSpan={4} />
              </Match>
              <Match when={data()}>
                {(result) => (
                  <For each={result().songs}>
                    {(song) => (
                      <tr class="hover:bg-primary/30 focus-within:bg-primary/30 transition-colors">
                        <td>
                          <a
                            href={`/songs/${song.songId}?back=${encodeURIComponent(window.location.search)}`}
                            class="link link-hover font-medium"
                          >
                            {song.title}
                          </a>
                        </td>
                        <td class="whitespace-nowrap">
                          <span class={`badge badge-sm badge-soft ${SONG_TYPE_BADGE_CLASS[song.type.value]}`}>
                            {song.type.name}
                          </span>
                        </td>
                        <td class="whitespace-nowrap">
                          <span class={`badge badge-sm ${song.isDisplay ? 'badge-success badge-soft' : 'badge-ghost'}`}>
                            {song.isDisplay ? '表示する' : '表示しない'}
                          </span>
                        </td>
                        <td>{song.orderNo}</td>
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
