import { Show, createResource, createSignal, For, Match, Switch } from 'solid-js';
import { client } from '../../utils/client';
import { ListState } from '../ListState';
import { Pagination } from '../Pagination';

const PER_PAGE_OPTIONS = [25, 50, 100] as const;
type PerPage = (typeof PER_PAGE_OPTIONS)[number];
type Sort = 'name' | 'order_no';
type Order = 'asc' | 'desc';

const DEFAULT_PARAMS = {
  name: '',
  sort: 'order_no' as Sort,
  order: 'asc' as Order,
  page: 1,
  perPage: 50 as PerPage,
};

const getInitialParams = (): { name: string; sort: Sort; order: Order; page: number; perPage: PerPage } => {
  const params = new URLSearchParams(window.location.search);
  const perPageRaw = Number(params.get('per_page'));
  const sort = params.get('sort');
  const order = params.get('order');
  return {
    name: params.get('name') ?? DEFAULT_PARAMS.name,
    sort: sort === 'name' || sort === 'order_no' ? sort : DEFAULT_PARAMS.sort,
    order: order === 'asc' || order === 'desc' ? order : DEFAULT_PARAMS.order,
    page: Number(params.get('page') ?? String(DEFAULT_PARAMS.page)) || DEFAULT_PARAMS.page,
    perPage: (PER_PAGE_OPTIONS.includes(perPageRaw as PerPage) ? perPageRaw : DEFAULT_PARAMS.perPage) as PerPage,
  };
};

export const SearchList = () => {
  const initial = getInitialParams();

  const [name, setName] = createSignal(initial.name);
  const [sort, setSort] = createSignal<Sort>(initial.sort);
  const [order, setOrder] = createSignal<Order>(initial.order);
  const [page, setPage] = createSignal(initial.page);
  const [perPage, setPerPage] = createSignal<PerPage>(initial.perPage);

  // 検索フォームの一時入力値(Submit前)
  const [inputName, setInputName] = createSignal(initial.name);
  const [inputSort, setInputSort] = createSignal<Sort>(initial.sort);
  const [inputOrder, setInputOrder] = createSignal<Order>(initial.order);
  const [inputPerPage, setInputPerPage] = createSignal<PerPage>(initial.perPage);

  const updateUrl = (params: { name: string; sort: Sort; order: Order; page: number; perPage: number }) => {
    const searchParams = new URLSearchParams();
    if (params.name) searchParams.set('name', params.name);
    if (params.sort) searchParams.set('sort', params.sort);
    if (params.order) searchParams.set('order', params.order);
    searchParams.set('page', String(params.page));
    searchParams.set('per_page', String(params.perPage));
    history.pushState(null, '', `?${searchParams.toString()}`);
  };

  const [fetchError, setFetchError] = createSignal<string | null>(null);

  const [data, { refetch }] = createResource(
    () => ({ name: name(), sort: sort(), order: order(), page: page(), perPage: perPage() }),
    async (params) => {
      setFetchError(null);

      const { data, status } = await client.api['song-tags'].search.get({
        query: {
          name: params.name,
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

    setName(inputName());
    setSort(inputSort());
    setOrder(inputOrder());
    setPerPage(inputPerPage());
    setPage(newPage);
    updateUrl({ name: inputName(), sort: inputSort(), order: inputOrder(), page: newPage, perPage: inputPerPage() });
  };

  const handlePageChange = (page: number) => {
    setPage(page);
    updateUrl({ name: name(), sort: sort(), order: order(), page: page, perPage: perPage() });
  };

  const handleReset = () => {
    setInputName(DEFAULT_PARAMS.name);
    setInputSort(DEFAULT_PARAMS.sort);
    setInputOrder(DEFAULT_PARAMS.order);
    setInputPerPage(DEFAULT_PARAMS.perPage);

    setName(DEFAULT_PARAMS.name);
    setSort(DEFAULT_PARAMS.sort);
    setOrder(DEFAULT_PARAMS.order);
    setPage(DEFAULT_PARAMS.page);
    setPerPage(DEFAULT_PARAMS.perPage);

    updateUrl(DEFAULT_PARAMS);
  };

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
            value={inputName()}
            onInput={e => setInputName(e.currentTarget.value)}
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
            onChange={e => setInputSort(e.currentTarget.value as Sort)}
          >
            <option value="order_no" selected={inputSort() === 'order_no'}>
              表示順
            </option>
            <option value="name" selected={inputSort() === 'name'}>
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
                {message => <ListState state="error" colSpan={2} message={message()} onRetry={() => refetch()} />}
              </Match>
              <Match when={data() && data()!.tags.length === 0}>
                <ListState state="empty" colSpan={2} message="条件に一致する楽曲タグはありません。" />
              </Match>
              <Match when={data()}>
                {result => (
                  <For each={result().tags}>
                    {tag => (
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
        <Pagination page={page()} maxPage={data()!.maxPage} onChange={handlePageChange} />
      </Show>
    </>
  );
};
