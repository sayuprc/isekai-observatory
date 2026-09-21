import { For, Match, Show, Switch, createResource, createSignal } from 'solid-js';
import type { ReleaseDistributionTypeValue, ReleaseTypeValue } from '../../generated';
import { client } from '../../utils/client';
import { ListState } from '../ListState';
import { Pagination } from '../Pagination';

const PER_PAGE_OPTIONS = [25, 50, 100] as const;
type PerPage = (typeof PER_PAGE_OPTIONS)[number];
type DisplayFilter = '' | 'true' | 'false';

const RELEASE_TYPE_OPTIONS: Array<{ value: '' | `${ReleaseTypeValue}`; label: string }> = [
  { value: '', label: 'すべて' },
  { value: '1', label: 'シングル' },
  { value: '2', label: 'アルバム' },
  { value: '3', label: 'EP' },
  { value: '99', label: 'その他' },
];

const DISTRIBUTION_TYPE_OPTIONS: Array<{ value: '' | `${ReleaseDistributionTypeValue}`; label: string }> = [
  { value: '', label: 'すべて' },
  { value: '1', label: '配信' },
  { value: '2', label: '物理' },
  { value: '99', label: 'その他' },
];

const DEFAULT_PARAMS = {
  title: '',
  type: '' as '' | `${ReleaseTypeValue}`,
  distributionType: '' as '' | `${ReleaseDistributionTypeValue}`,
  isDisplay: '' as DisplayFilter,
  page: 1,
  perPage: 25 as PerPage,
};

const getInitialParams = () => {
  const params = new URLSearchParams(window.location.search);
  const perPageRaw = Number(params.get('per_page'));

  return {
    title: params.get('title') ?? DEFAULT_PARAMS.title,
    type: (params.get('type') ?? DEFAULT_PARAMS.type) as '' | `${ReleaseTypeValue}`,
    distributionType: (params.get('distribution_type') ?? DEFAULT_PARAMS.distributionType) as
    | ''
    | `${ReleaseDistributionTypeValue}`,
    isDisplay: (params.get('is_display') ?? DEFAULT_PARAMS.isDisplay) as DisplayFilter,
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

const buildDetailHref = (releaseId: string): string => {
  const back = typeof window === 'undefined' ? '' : window.location.search;
  return back ? `/releases/${releaseId}?back=${encodeURIComponent(back)}` : `/releases/${releaseId}`;
};

export const SearchList = () => {
  const initial = getInitialParams();

  const [title, setTitle] = createSignal(initial.title);
  const [type, setType] = createSignal(initial.type);
  const [distributionType, setDistributionType] = createSignal(initial.distributionType);
  const [isDisplay, setIsDisplay] = createSignal<DisplayFilter>(initial.isDisplay);
  const [page, setPage] = createSignal(initial.page);
  const [perPage, setPerPage] = createSignal<PerPage>(initial.perPage);

  const [inputTitle, setInputTitle] = createSignal(initial.title);
  const [inputType, setInputType] = createSignal(initial.type);
  const [inputDistributionType, setInputDistributionType] = createSignal(initial.distributionType);
  const [inputIsDisplay, setInputIsDisplay] = createSignal<DisplayFilter>(initial.isDisplay);
  const [inputPerPage, setInputPerPage] = createSignal<PerPage>(initial.perPage);

  const updateUrl = (params: {
    title: string;
    type: string;
    distributionType: string;
    isDisplay: DisplayFilter;
    page: number;
    perPage: number;
  }) => {
    const searchParams = new URLSearchParams();
    if (params.title) searchParams.set('title', params.title);
    if (params.type) searchParams.set('type', params.type);
    if (params.distributionType) searchParams.set('distribution_type', params.distributionType);
    if (params.isDisplay) searchParams.set('is_display', params.isDisplay);
    searchParams.set('page', String(params.page));
    searchParams.set('per_page', String(params.perPage));
    history.pushState(null, '', `?${searchParams.toString()}`);
  };

  const [fetchError, setFetchError] = createSignal<string | null>(null);

  const [data, { refetch }] = createResource(
    () => ({
      title: title(),
      type: type(),
      distributionType: distributionType(),
      isDisplay: isDisplay(),
      page: page(),
      perPage: perPage(),
    }),
    async (params) => {
      setFetchError(null);

      const { data, status } = await client.api.releases.search.get({
        query: {
          title: params.title,
          type: params.type || undefined,
          distribution_type: params.distributionType || undefined,
          is_display: params.isDisplay === '' ? undefined : params.isDisplay === 'true',
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
    setDistributionType(inputDistributionType());
    setIsDisplay(inputIsDisplay());
    setPerPage(inputPerPage());
    setPage(newPage);
    updateUrl({
      title: inputTitle(),
      type: inputType(),
      distributionType: inputDistributionType(),
      isDisplay: inputIsDisplay(),
      page: newPage,
      perPage: inputPerPage(),
    });
  };

  const handlePageChange = (nextPage: number) => {
    setPage(nextPage);
    updateUrl({
      title: title(),
      type: type(),
      distributionType: distributionType(),
      isDisplay: isDisplay(),
      page: nextPage,
      perPage: perPage(),
    });
  };

  const handleReset = () => {
    setInputTitle(DEFAULT_PARAMS.title);
    setInputType(DEFAULT_PARAMS.type);
    setInputDistributionType(DEFAULT_PARAMS.distributionType);
    setInputIsDisplay(DEFAULT_PARAMS.isDisplay);
    setInputPerPage(DEFAULT_PARAMS.perPage);
    setTitle(DEFAULT_PARAMS.title);
    setType(DEFAULT_PARAMS.type);
    setDistributionType(DEFAULT_PARAMS.distributionType);
    setIsDisplay(DEFAULT_PARAMS.isDisplay);
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
            placeholder="リリース名で検索"
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
            onChange={e => setInputType(e.currentTarget.value as '' | `${ReleaseTypeValue}`)}
          >
            <For each={RELEASE_TYPE_OPTIONS}>
              {option => (
                <option value={option.value} selected={inputType() === option.value}>
                  {option.label}
                </option>
              )}
            </For>
          </select>
        </fieldset>
        <fieldset class="fieldset">
          <label class="fieldset-label" for="distributionType">
            流通形態
          </label>
          <select
            id="distributionType"
            name="distributionType"
            class="select select-bordered select-sm"
            onChange={e => setInputDistributionType(e.currentTarget.value as '' | `${ReleaseDistributionTypeValue}`)}
          >
            <For each={DISTRIBUTION_TYPE_OPTIONS}>
              {option => (
                <option value={option.value} selected={inputDistributionType() === option.value}>
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
        <a href="/releases/create" class="btn btn-primary btn-sm">
          新規作成
        </a>
      </div>

      <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
        <table class="table table-sm table-zebra md:table-md">
          <thead>
            <tr>
              <th>タイトル</th>
              <th>種別</th>
              <th>流通形態</th>
              <th>発売日</th>
              <th>表示設定</th>
            </tr>
          </thead>
          <tbody>
            <Switch>
              <Match when={data.loading}>
                <ListState state="loading" colSpan={5} />
              </Match>
              <Match when={fetchError()}>
                {message => <ListState state="error" colSpan={5} message={message()} onRetry={() => refetch()} />}
              </Match>
              <Match when={data() && data()!.releases.length === 0}>
                <ListState state="empty" colSpan={5} message="条件に一致するリリースはありません。" />
              </Match>
              <Match when={data()}>
                {result => (
                  <For each={result().releases}>
                    {release => (
                      <tr class="transition-colors hover:bg-primary/30 focus-within:bg-primary/30">
                        <td class="min-w-56">
                          <a href={buildDetailHref(release.releaseId)} class="link link-hover font-medium">
                            {release.title}
                          </a>
                        </td>
                        <td>
                          {RELEASE_TYPE_OPTIONS.find(option => option.value === String(release.typeValue))?.label
                            ?? '不明'}
                        </td>
                        <td>
                          {DISTRIBUTION_TYPE_OPTIONS.find(
                            option => option.value === String(release.distributionTypeValue),
                          )?.label ?? '不明'}
                        </td>
                        <td class="whitespace-nowrap text-sm">{normalizeDateDisplayValue(release.releasedOn)}</td>
                        <td>
                          <span
                            class={`badge badge-sm ${release.isDisplay ? 'badge-success badge-soft' : 'badge-ghost'}`}
                          >
                            {release.isDisplay ? '表示する' : '表示しない'}
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
