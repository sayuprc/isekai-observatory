import { Show, createResource, createSignal, For, Match, Switch } from 'solid-js';
import type { SongSearchTypeValue, SongTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { ListState } from '../ListState';
import { Pagination } from '../Pagination';

const SONG_TYPE_BADGE_CLASS: Record<SongTypeValue, string> = {
  1: 'badge-warning',
  2: 'badge-info',
};

const PER_PAGE_OPTIONS = [25, 50, 100] as const;
type PerPage = (typeof PER_PAGE_OPTIONS)[number];
type Sort = 'title' | 'order_no';
type Order = 'asc' | 'desc';

const DEFAULT_PARAMS = {
  title: '',
  type: undefined,
  isDisplay: undefined,
  sort: 'order_no' as Sort,
  order: 'asc' as Order,
  page: 1,
  perPage: 25 as PerPage,
};

const getInitialParams = (): {
  title: string;
  type?: SongSearchTypeValue;
  isDisplay?: boolean;
  sort: Sort;
  order: Order;
  page: number;
  perPage: PerPage;
} => {
  const params = new URLSearchParams(window.location.search);
  const perPageRaw = Number(params.get('per_page'));
  const isDisplayRaw = params.get('is_display');
  const sort = params.get('sort');
  const order = params.get('order');
  const type = params.get('type');

  return {
    title: params.get('title') ?? DEFAULT_PARAMS.title,
    type: type === '1' || type === '2' ? type : undefined,
    isDisplay: isDisplayRaw === 'true' ? true : isDisplayRaw === 'false' ? false : undefined,
    sort: sort === 'title' || sort === 'order_no' ? sort : DEFAULT_PARAMS.sort,
    order: order === 'asc' || order === 'desc' ? order : DEFAULT_PARAMS.order,
    page: Number(params.get('page') ?? String(DEFAULT_PARAMS.page)) || DEFAULT_PARAMS.page,
    perPage: (PER_PAGE_OPTIONS.includes(perPageRaw as PerPage) ? perPageRaw : DEFAULT_PARAMS.perPage) as PerPage,
  };
};

export const SearchList = () => {
  const initial = getInitialParams();

  const [title, setTitle] = createSignal(initial.title);
  const [type, setType] = createSignal(initial.type);
  const [isDisplay, setIsDisplay] = createSignal(initial.isDisplay);
  const [sort, setSort] = createSignal<Sort>(initial.sort);
  const [order, setOrder] = createSignal<Order>(initial.order);
  const [page, setPage] = createSignal(initial.page);
  const [perPage, setPerPage] = createSignal<PerPage>(initial.perPage);

  // 検索フォームの一時入力値(Submit前)
  const [inputTitle, setInputTitle] = createSignal(initial.title);
  const [inputType, setInputType] = createSignal(initial.type);
  const [inputIsDisplay, setInputIsDisplay] = createSignal(initial.isDisplay);
  const [inputSort, setInputSort] = createSignal<Sort>(initial.sort);
  const [inputOrder, setInputOrder] = createSignal<Order>(initial.order);
  const [inputPerPage, setInputPerPage] = createSignal<PerPage>(initial.perPage);

  const updateUrl = (params: {
    title: string;
    type?: SongSearchTypeValue;
    isDisplay?: boolean;
    sort: Sort;
    order: Order;
    page: number;
    perPage: number;
  }) => {
    const searchParams = new URLSearchParams();
    if (params.title) searchParams.set('title', params.title);
    if (params.type) searchParams.set('type', String(params.type));
    if (params.isDisplay !== undefined) searchParams.set('is_display', String(params.isDisplay));
    if (params.sort) searchParams.set('sort', params.sort);
    if (params.order) searchParams.set('order', params.order);
    searchParams.set('page', String(params.page));
    searchParams.set('per_page', String(params.perPage));
    history.pushState(null, '', `?${searchParams.toString()}`);
  };

  const [fetchError, setFetchError] = createSignal<string | null>(null);

  const [data, { refetch }] = createResource(
    () => ({
      title: title(),
      type: type(),
      isDisplay: isDisplay(),
      sort: sort(),
      order: order(),
      page: page(),
      perPage: perPage(),
    }),
    async (params) => {
      setFetchError(null);

      const { data, status } = await client.api.songs.search.get({
        query: {
          title: params.title,
          type: params.type,
          is_display: params.isDisplay,
          sort: params.sort,
          order: params.order,
          page: params.page,
          per_page: params.perPage,
        },
      });

      if (status === 401) {
        window.location.href = '/auth/login';
        return;
      }

      if (!data) {
        setFetchError('データの取得に失敗しました。再度お試しください。');
        return;
      }

      return data;
    },
  );

  const handleSearch = (e: Event) => {
    e.preventDefault();

    // 検索時は必ず 1 ページ目に戻る
    const newPage = 1;

    setTitle(inputTitle());
    setType(inputType());
    setIsDisplay(inputIsDisplay());
    setSort(inputSort());
    setOrder(inputOrder());
    setPerPage(inputPerPage());
    setPage(newPage);
    updateUrl({
      title: inputTitle(),
      type: inputType(),
      isDisplay: inputIsDisplay(),
      sort: inputSort(),
      order: inputOrder(),
      page: newPage,
      perPage: inputPerPage(),
    });
  };

  const handlePageChange = (page: number) => {
    setPage(page);
    updateUrl({
      title: title(),
      type: type(),
      isDisplay: isDisplay(),
      sort: sort(),
      order: order(),
      page: page,
      perPage: perPage(),
    });
  };

  const handleReset = () => {
    setInputTitle(DEFAULT_PARAMS.title);
    setInputType(DEFAULT_PARAMS.type);
    setInputIsDisplay(DEFAULT_PARAMS.isDisplay);
    setInputSort(DEFAULT_PARAMS.sort);
    setInputOrder(DEFAULT_PARAMS.order);
    setInputPerPage(DEFAULT_PARAMS.perPage);

    setTitle(DEFAULT_PARAMS.title);
    setType(DEFAULT_PARAMS.type);
    setIsDisplay(DEFAULT_PARAMS.isDisplay);
    setSort(DEFAULT_PARAMS.sort);
    setOrder(DEFAULT_PARAMS.order);
    setPerPage(DEFAULT_PARAMS.perPage);
    setPage(DEFAULT_PARAMS.page);

    updateUrl(DEFAULT_PARAMS);
  };

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
            value={inputTitle()}
            onInput={e => setInputTitle(e.currentTarget.value)}
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
            onChange={e =>
              setInputType(e.currentTarget.value !== '' ? (e.currentTarget.value as SongSearchTypeValue) : undefined)}
          >
            <option value="" selected={inputType() === undefined}>
              すべて
            </option>
            <For each={data()?.types ?? []}>
              {t => (
                <option value={String(t.value)} selected={inputType() === String(t.value)}>
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
            onChange={e =>
              setInputIsDisplay(e.currentTarget.value === '' ? undefined : e.currentTarget.value === 'true')}
          >
            <option value="" selected={inputIsDisplay() === undefined}>
              すべて
            </option>
            <option value="true" selected={inputIsDisplay() === true}>
              表示する
            </option>
            <option value="false" selected={inputIsDisplay() === false}>
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
            onChange={e => setInputSort(e.currentTarget.value as Sort)}
          >
            <option value="order_no" selected={inputSort() === 'order_no'}>
              表示順
            </option>
            <option value="title" selected={inputSort() === 'title'}>
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
            onChange={e => setInputOrder(e.currentTarget.value as Order)}
          >
            <option value="asc" selected={inputOrder() === 'asc'}>
              昇順
            </option>
            <option value="desc" selected={inputOrder() === 'desc'}>
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
            onChange={e => setInputPerPage(Number(e.currentTarget.value) as PerPage)}
          >
            <For each={PER_PAGE_OPTIONS}>
              {n => (
                <option value={n} selected={inputPerPage() === n}>
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
                {message => <ListState state="error" colSpan={4} message={message()} onRetry={() => refetch()} />}
              </Match>
              <Match when={data() && data()!.songs.length === 0}>
                <ListState state="empty" colSpan={4} />
              </Match>
              <Match when={data()}>
                {result => (
                  <For each={result().songs}>
                    {song => (
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
        <Pagination page={page()} maxPage={data()!.maxPage} onChange={handlePageChange} />
      </Show>
    </>
  );
};
