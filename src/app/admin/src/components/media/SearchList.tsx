import { createSignal, For, Match, Show, Switch } from 'solid-js';
import type { MediaSearchSortBy, MediaTypeValue, SortOrder } from '../../generated';
import { client } from '../../utils/client';
import { normalizeDateTimeDisplayValue } from '../../utils/date';
import {
  PER_PAGE_OPTIONS,
  createSearchResource,
  createSearchState,
  parsePage,
  pickParam,
} from '../../utils/search-list';
import type { PerPageOption as PerPage } from '../../utils/search-list';
import { CountCell } from '../CountCell';
import { MetaChip } from '../EntityHeader';
import { ListWithPreview, RecordPreview, selectedRowClass, type RecordPreviewData } from '../ListPreview';
import { ListState } from '../ListState';
import { Pagination } from '../Pagination';

type DisplayFilter = '' | 'true' | 'false';
type Sort = MediaSearchSortBy;
type Order = SortOrder;

const MEDIA_TYPE_OPTIONS: Array<{ value: '' | `${MediaTypeValue}`; label: string }> = [
  { value: '', label: 'すべて' },
  { value: '1', label: 'MV' },
  { value: '2', label: '音源動画' },
  { value: '3', label: '配信' },
  { value: '4', label: 'ショート' },
  { value: '5', label: '投稿' },
  { value: '99', label: 'その他' },
];

/** 一覧では URL 全体は幅に見合わないため、投稿先が分かるホスト名だけを出す(解析できない値は素のまま表示する) */
const toHostLabel = (url: string): string => {
  try {
    return new URL(url).hostname.replace(/^www\./, '');
  } catch {
    return url;
  }
};

type SearchParams = {
  title: string;
  type: '' | `${MediaTypeValue}`;
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
  sort: 'published_at',
  order: 'asc',
  page: 1,
  perPage: 25,
};

const parseParams = (query: URLSearchParams): SearchParams => ({
  title: query.get('title') ?? '',
  type: pickParam(
    query.get('type'),
    MEDIA_TYPE_OPTIONS.map((option) => option.value),
    '',
  ),
  isDisplay: pickParam(query.get('is_display'), ['true', 'false'] as const, ''),
  sort: pickParam(query.get('sort'), ['published_at', 'title'] as const, DEFAULT_PARAMS.sort),
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

  const { data, refetch, fetchError } = createSearchResource(params, (current) =>
    client.api.media.search.get({
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

  // 右側のプレビューに出す行。取得結果の中から引くので、ページを移ると外れる
  const [selectedId, setSelectedId] = createSignal<string | null>(null);
  const detailUrl = (mediaId: string) => `/media/${mediaId}?back=${encodeURIComponent(window.location.search)}`;
  const selectedRecord = (): RecordPreviewData | undefined => {
    const media = data()?.media.find((candidate) => candidate.mediaId === selectedId());
    if (!media) return undefined;
    return {
      title: media.title,
      meta: (
        <>
          <MetaChip>{media.type.name}</MetaChip>
          <MetaChip dot={media.isDisplay ? 'success' : 'muted'}>{media.isDisplay ? '公開' : '非公開'}</MetaChip>
        </>
      ),
      rows: [
        { label: '公開日', value: normalizeDateTimeDisplayValue(media.publishedAt) },
        { label: 'リンクしている楽曲', value: media.songCount },
        { label: '関連づけているイベント', value: media.eventCount },
      ],
      actions: [
        { label: '開く', href: detailUrl(media.mediaId), primary: true },
        { label: '元のページ', href: media.url, external: true },
      ],
    };
  };

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
            onInput={(e) => updateInput({ title: e.currentTarget.value })}
            class="input input-bordered input-sm"
            placeholder="メディアタイトルで検索"
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
            onChange={(e) => updateInput({ type: e.currentTarget.value as '' | `${MediaTypeValue}` })}
          >
            <For each={MEDIA_TYPE_OPTIONS}>
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
            onChange={(e) => updateInput({ isDisplay: e.currentTarget.value as DisplayFilter })}
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
            onChange={(e) => updateInput({ sort: e.currentTarget.value as Sort })}
          >
            <option value="published_at" selected={input().sort === 'published_at'}>
              公開日
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
        <a href={`/media/create?back=${encodeURIComponent(window.location.search)}`} class="btn btn-primary btn-sm">
          新規作成
        </a>
      </div>

      <ListWithPreview
        table={
          <table class="table table-sm">
            <thead>
              <tr>
                <th>タイトル</th>
                <th>公開日</th>
                <th>種別</th>
                <th class="text-right">楽曲</th>
                <th class="text-right">イベント</th>
                <th>公開</th>
                <th>リンク</th>
              </tr>
            </thead>
            <tbody>
              <Switch>
                <Match when={data.loading}>
                  <ListState state="loading" colSpan={7} />
                </Match>
                <Match when={fetchError()}>
                  {(message) => <ListState state="error" colSpan={7} message={message()} onRetry={() => refetch()} />}
                </Match>
                <Match when={data() && data()!.media.length === 0}>
                  <ListState state="empty" colSpan={7} message="条件に一致するメディアはありません。" />
                </Match>
                <Match when={data()}>
                  {(result) => (
                    <For each={result().media}>
                      {(media) => (
                        <tr
                          class="cursor-pointer hover:bg-base-200"
                          classList={{ [selectedRowClass]: media.mediaId === selectedId() }}
                          onClick={() => setSelectedId(media.mediaId)}
                          onFocusIn={() => setSelectedId(media.mediaId)}
                        >
                          <td class="max-w-72 min-w-44">
                            <a href={detailUrl(media.mediaId)} class="link link-hover block truncate font-medium">
                              {media.title}
                            </a>
                          </td>
                          <td class="font-mono text-xs whitespace-nowrap">
                            {normalizeDateTimeDisplayValue(media.publishedAt)}
                          </td>
                          <td class="whitespace-nowrap">{media.type.name}</td>
                          <CountCell count={media.songCount} />
                          <CountCell count={media.eventCount} />
                          <td class="whitespace-nowrap">
                            <span
                              class={`badge badge-sm ${media.isDisplay ? 'badge-success badge-soft' : 'badge-ghost'}`}
                            >
                              {media.isDisplay ? '公開' : '非公開'}
                            </span>
                          </td>
                          <td class="max-w-40">
                            <a
                              href={media.url}
                              target="_blank"
                              rel="noreferrer"
                              title={media.url}
                              class="link link-hover block truncate text-sm whitespace-nowrap"
                            >
                              {toHostLabel(media.url)}
                            </a>
                          </td>
                        </tr>
                      )}
                    </For>
                  )}
                </Match>
              </Switch>
            </tbody>
          </table>
        }
        preview={<RecordPreview label="選択中のメディア" record={selectedRecord()} />}
      />

      <Show when={!data.loading && !fetchError() && (data()?.maxPage ?? 0) > 1}>
        <Pagination page={params().page} maxPage={data()!.maxPage} onChange={handlePageChange} />
      </Show>
    </>
  );
};
