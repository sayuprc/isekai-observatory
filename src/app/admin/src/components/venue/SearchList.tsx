import { For, Match, Show, Switch, createResource, createSignal } from 'solid-js';
import type { SortOrder, VenueKindValue, VenueSearchSortBy } from '../../generated';
import { client } from '../../utils/client';
import { ListState } from '../ListState';
import { Pagination } from '../Pagination';

const PER_PAGE_OPTIONS = [25, 50, 100] as const;
type PerPage = (typeof PER_PAGE_OPTIONS)[number];
type KindFilter = '' | `${VenueKindValue}`;
type SortBy = VenueSearchSortBy;

const DEFAULT_PARAMS = {
  name: '',
  kind: '' as KindFilter,
  sort: 'name' as SortBy,
  order: 'asc' as SortOrder,
  page: 1,
  perPage: 25 as PerPage,
};

const getInitialParams = () => {
  const params = new URLSearchParams(window.location.search);
  const perPageRaw = Number(params.get('per_page'));
  const kind = params.get('kind');
  const sort = params.get('sort');

  return {
    name: params.get('name') ?? '',
    kind: (kind === '1' || kind === '2' ? kind : '') as KindFilter,
    sort: (sort === 'name' ? sort : 'name') as SortBy,
    order: (params.get('order') === 'desc' ? 'desc' : 'asc') as SortOrder,
    page: Number(params.get('page') ?? '1') || 1,
    perPage: (PER_PAGE_OPTIONS.includes(perPageRaw as PerPage) ? perPageRaw : 25) as PerPage,
  };
};

export const SearchList = () => {
  const initial = getInitialParams();
  const [name, setName] = createSignal(initial.name);
  const [kind, setKind] = createSignal<KindFilter>(initial.kind);
  const [sort, setSort] = createSignal<SortBy>(initial.sort);
  const [order, setOrder] = createSignal(initial.order);
  const [page, setPage] = createSignal(initial.page);
  const [perPage, setPerPage] = createSignal<PerPage>(initial.perPage);
  const [inputName, setInputName] = createSignal(initial.name);
  const [inputKind, setInputKind] = createSignal<KindFilter>(initial.kind);
  const [inputSort, setInputSort] = createSignal<SortBy>(initial.sort);
  const [inputOrder, setInputOrder] = createSignal(initial.order);
  const [inputPerPage, setInputPerPage] = createSignal<PerPage>(initial.perPage);
  const [fetchError, setFetchError] = createSignal<string | null>(null);

  const updateUrl = (params: typeof DEFAULT_PARAMS) => {
    const query = new URLSearchParams();
    if (params.name) query.set('name', params.name);
    if (params.kind) query.set('kind', params.kind);
    query.set('sort', params.sort);
    query.set('order', params.order);
    query.set('page', String(params.page));
    query.set('per_page', String(params.perPage));
    history.pushState(null, '', `?${query.toString()}`);
  };

  const [data, { refetch }] = createResource(
    () => ({ name: name(), kind: kind(), sort: sort(), order: order(), page: page(), perPage: perPage() }),
    async (params) => {
      setFetchError(null);
      const response = await client.api.venues.search.get({
        query: {
          name: params.name,
          kind: params.kind ? Number(params.kind) as VenueKindValue : undefined,
          sort: params.sort,
          order: params.order,
          page: params.page,
          per_page: params.perPage,
        },
      });

      if (response.status === 401) {
        window.location.href = '/auth/login';
        return;
      }
      if (response.status === 403) {
        setFetchError('開催先の閲覧権限がありません');
        return;
      }
      if (!response.data) {
        setFetchError('データの取得に失敗しました。再度お試しください。');
        return;
      }
      return response.data;
    },
  );

  const handleSearch = (event: Event) => {
    event.preventDefault();
    const next = {
      name: inputName(),
      kind: inputKind(),
      sort: inputSort(),
      order: inputOrder(),
      page: 1,
      perPage: inputPerPage(),
    };
    setName(next.name);
    setKind(next.kind);
    setSort(next.sort);
    setOrder(next.order);
    setPage(next.page);
    setPerPage(next.perPage);
    updateUrl(next);
  };

  const handlePageChange = (nextPage: number) => {
    setPage(nextPage);
    updateUrl({ name: name(), kind: kind(), sort: sort(), order: order(), page: nextPage, perPage: perPage() });
  };

  const handleReset = () => {
    setInputName('');
    setInputKind('');
    setInputSort('name');
    setInputOrder('asc');
    setInputPerPage(25);
    setName('');
    setKind('');
    setSort('name');
    setOrder('asc');
    setPage(1);
    setPerPage(25);
    updateUrl(DEFAULT_PARAMS);
  };

  return (
    <>
      <form onSubmit={handleSearch} class="mb-4 flex flex-wrap items-end gap-4">
        <fieldset class="fieldset">
          <label class="fieldset-label" for="name">開催先名</label>
          <input
            id="name"
            class="input input-bordered input-sm"
            value={inputName()}
            onInput={e => setInputName(e.currentTarget.value)}
            placeholder="開催先名で検索"
          />
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="kind">種別</label>
          <select
            id="kind"
            class="select select-bordered select-sm"
            onChange={e => setInputKind(e.currentTarget.value as KindFilter)}
          >
            <option value="" selected={inputKind() === ''}>すべて</option>
            <option value="1" selected={inputKind() === '1'}>現地</option>
            <option value="2" selected={inputKind() === '2'}>オンライン</option>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="sort">ソート項目</label>
          <select
            id="sort"
            class="select select-bordered select-sm"
            onChange={e => setInputSort(e.currentTarget.value as SortBy)}
          >
            <option value="name" selected={inputSort() === 'name'}>開催先名</option>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="order">並び順</label>
          <select
            id="order"
            class="select select-bordered select-sm"
            onChange={e => setInputOrder(e.currentTarget.value as SortOrder)}
          >
            <option value="asc" selected={inputOrder() === 'asc'}>昇順</option>
            <option value="desc" selected={inputOrder() === 'desc'}>降順</option>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="perPage">表示件数</label>
          <select
            id="perPage"
            class="select select-bordered select-sm"
            onChange={e => setInputPerPage(Number(e.currentTarget.value) as PerPage)}
          >
            <For each={PER_PAGE_OPTIONS}>{n => <option value={n} selected={inputPerPage() === n}>{n}件</option>}</For>
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
        <Pagination page={page()} maxPage={data()!.maxPage} onChange={handlePageChange} />
      </Show>
    </>
  );
};
