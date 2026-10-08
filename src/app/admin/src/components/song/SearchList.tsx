import { For, Match, Show, Switch } from 'solid-js';
import type { SongSearchTypeValue, SongTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { SONG_TYPE_NAMES, toOptions } from '../../utils/enum-names';
import {
  PER_PAGE_OPTIONS,
  createSearchResource,
  createSearchState,
  parsePage,
  pickParam,
} from '../../utils/search-list';
import type { PerPageOption as PerPage } from '../../utils/search-list';
import { CountCell } from '../CountCell';
import { ListState } from '../ListState';
import { Pagination } from '../Pagination';

const SONG_TYPE_BADGE_CLASS: Record<SongTypeValue, string> = {
  1: 'badge-warning',
  2: 'badge-info',
};

const SONG_TYPE_OPTIONS = toOptions(SONG_TYPE_NAMES).map((option) => ({
  value: `${option.value}` as const,
  label: option.label,
}));

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
    type:
      pickParam<SongSearchTypeValue | ''>(
        query.get('type'),
        SONG_TYPE_OPTIONS.map((option) => option.value),
        '',
      ) || undefined,
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

  const detailUrl = (songId: string) => `/songs/${songId}?back=${encodeURIComponent(window.location.search)}`;
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
            <For each={SONG_TYPE_OPTIONS}>
              {(option) => (
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
            onChange={(e) =>
              updateInput({ isDisplay: e.currentTarget.value === '' ? undefined : e.currentTarget.value === 'true' })
            }
          >
            <option value="" selected={input().isDisplay === undefined}>
              すべて
            </option>
            <option value="true" selected={input().isDisplay === true}>
              表示
            </option>
            <option value="false" selected={input().isDisplay === false}>
              非表示
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
      <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
        <table class="table table-sm table-zebra md:table-md">
          <thead>
            <tr>
              <th>楽曲名</th>
              <th>楽曲種別</th>
              <th class="text-right">披露</th>
              <th class="text-right">メディア</th>
              <th class="text-right">クレジット</th>
              <th class="text-right">リリース</th>
              <th>公開</th>
              <th>表示順</th>
            </tr>
          </thead>
          <tbody>
            <Switch>
              <Match when={data.loading}>
                <ListState state="loading" colSpan={8} />
              </Match>
              <Match when={fetchError()}>
                {(message) => <ListState state="error" colSpan={8} message={message()} onRetry={() => refetch()} />}
              </Match>
              <Match when={data() && data()!.songs.length === 0}>
                <ListState state="empty" colSpan={8} />
              </Match>
              <Match when={data()}>
                {(result) => (
                  <For each={result().songs}>
                    {(song) => (
                      <tr class="transition-colors hover:bg-primary/30 focus-within:bg-primary/30">
                        <td>
                          <a href={detailUrl(song.songId)} class="link link-hover font-medium">
                            {song.title}
                          </a>
                        </td>
                        <td class="whitespace-nowrap">
                          <span class={`badge badge-sm badge-soft ${SONG_TYPE_BADGE_CLASS[song.type]}`}>
                            {SONG_TYPE_NAMES[song.type]}
                          </span>
                        </td>
                        <CountCell count={song.performanceCount} />
                        <CountCell count={song.mediaCount} />
                        <CountCell count={song.personCount} />
                        <CountCell count={song.releaseCount} />
                        <td class="whitespace-nowrap">
                          <span class={`badge badge-sm ${song.isDisplay ? 'badge-success badge-soft' : 'badge-ghost'}`}>
                            {song.isDisplay ? '公開' : '非公開'}
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
