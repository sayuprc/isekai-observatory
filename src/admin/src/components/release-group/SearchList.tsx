import { For, Match, Show, Switch, createResource, createSignal } from 'solid-js';
import type { ReleaseGroupSearchSortBy, ReleaseGroupTypeValue, SortOrder } from '../../generated';
import { client } from '../../utils/client';
import { ListState } from '../ListState';
import { Pagination } from '../Pagination';

const PER_PAGE_OPTIONS = [25, 50, 100] as const;
type PerPage = (typeof PER_PAGE_OPTIONS)[number];
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

const DEFAULT_PARAMS = {
  title: '',
  type: '' as '' | `${ReleaseGroupTypeValue}`,
  isDisplay: '' as DisplayFilter,
  sort: 'first_released_on' as Sort,
  order: 'desc' as Order,
  page: 1,
  perPage: 25 as PerPage,
};

const getInitialParams = () => {
  const params = new URLSearchParams(window.location.search);
  const perPageRaw = Number(params.get('per_page'));

  const sort = params.get('sort');
  const order = params.get('order');

  return {
    title: params.get('title') ?? DEFAULT_PARAMS.title,
    type: (params.get('type') ?? DEFAULT_PARAMS.type) as '' | `${ReleaseGroupTypeValue}`,
    isDisplay: (params.get('is_display') ?? DEFAULT_PARAMS.isDisplay) as DisplayFilter,
    sort: sort === 'first_released_on' || sort === 'title' ? sort : DEFAULT_PARAMS.sort,
    order: order === 'asc' || order === 'desc' ? order : DEFAULT_PARAMS.order,
    page: Number(params.get('page') ?? String(DEFAULT_PARAMS.page)) || DEFAULT_PARAMS.page,
    perPage: (PER_PAGE_OPTIONS.includes(perPageRaw as PerPage) ? perPageRaw : DEFAULT_PARAMS.perPage) as PerPage,
  };
};

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
  return back ? `/release-groups/${releaseGroupId}?back=${encodeURIComponent(back)}` : `/release-groups/${releaseGroupId}`;
};

export const SearchList = () => {
  const initial = getInitialParams();

  const [title, setTitle] = createSignal(initial.title);
  const [type, setType] = createSignal(initial.type);
  const [isDisplay, setIsDisplay] = createSignal<DisplayFilter>(initial.isDisplay);
  const [sort, setSort] = createSignal<Sort>(initial.sort);
  const [order, setOrder] = createSignal<Order>(initial.order);
  const [page, setPage] = createSignal(initial.page);
  const [perPage, setPerPage] = createSignal<PerPage>(initial.perPage);

  const [inputTitle, setInputTitle] = createSignal(initial.title);
  const [inputType, setInputType] = createSignal(initial.type);
  const [inputIsDisplay, setInputIsDisplay] = createSignal<DisplayFilter>(initial.isDisplay);
  const [inputSort, setInputSort] = createSignal<Sort>(initial.sort);
  const [inputOrder, setInputOrder] = createSignal<Order>(initial.order);
  const [inputPerPage, setInputPerPage] = createSignal<PerPage>(initial.perPage);

  const updateUrl = (params: {
    title: string;
    type: string;
    isDisplay: DisplayFilter;
    sort: Sort;
    order: Order;
    page: number;
    perPage: number;
  }) => {
    const searchParams = new URLSearchParams();
    if (params.title) searchParams.set('title', params.title);
    if (params.type) searchParams.set('type', params.type);
    if (params.isDisplay) searchParams.set('is_display', params.isDisplay);
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

      const { data, status } = await client.api['release-groups'].search.get({
        query: {
          title: params.title,
          type: params.type || undefined,
          is_display: params.isDisplay === '' ? undefined : params.isDisplay === 'true',
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

  const handlePageChange = (nextPage: number) => {
    setPage(nextPage);
    updateUrl({
      title: title(),
      type: type(),
      isDisplay: isDisplay(),
      sort: sort(),
      order: order(),
      page: nextPage,
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
    setPage(DEFAULT_PARAMS.page);
    setPerPage(DEFAULT_PARAMS.perPage);
    updateUrl(DEFAULT_PARAMS);
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
            value={inputTitle()}
            onInput={e => setInputTitle(e.currentTarget.value)}
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
            onChange={e => setInputType(e.currentTarget.value as '' | `${ReleaseGroupTypeValue}`)}
          >
            <For each={RELEASE_GROUP_TYPE_OPTIONS}>
              {option => (
                <option value={option.value} selected={inputType() === option.value}>
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
            onChange={e => setInputIsDisplay(e.currentTarget.value as DisplayFilter)}
          >
            <option value="" selected={inputIsDisplay() === ''}>
              すべて
            </option>
            <option value="true" selected={inputIsDisplay() === 'true'}>
              表示する
            </option>
            <option value="false" selected={inputIsDisplay() === 'false'}>
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
            <option value="first_released_on" selected={inputSort() === 'first_released_on'}>
              初リリース日
            </option>
            <option value="title" selected={inputSort() === 'title'}>
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
            onChange={e => setInputOrder(e.currentTarget.value as Order)}
          >
            <option value="desc" selected={inputOrder() === 'desc'}>
              降順
            </option>
            <option value="asc" selected={inputOrder() === 'asc'}>
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
        <Pagination page={page()} maxPage={data()!.maxPage} onChange={handlePageChange} />
      </Show>
    </>
  );
};
