import { createResource, createSignal } from 'solid-js';
import type { Accessor } from 'solid-js';
import { redirectToLogin } from './auth-redirect';

export const PER_PAGE_OPTIONS = [25, 50, 100] as const;
export type PerPageOption = (typeof PER_PAGE_OPTIONS)[number];

const FETCH_ERROR_MESSAGE = 'データの取得に失敗しました。再度お試しください。';

type QueryValue = string | number | boolean | undefined;

/** URL の値が候補にあればそれを、なければ fallback を返す */
export const pickParam = <T extends string | number>(value: string | null, candidates: readonly T[], fallback: T): T =>
  candidates.find(candidate => String(candidate) === value) ?? fallback;

export const parsePage = (value: string | null): number => Math.max(1, Number(value) || 1);

interface SearchStateOptions<P extends { page: number }> {
  defaults: P;
  parse: (query: URLSearchParams) => P;
  /** URL に載せるクエリ。undefined と空文字のキーは省く */
  toQuery: (params: P) => Record<string, QueryValue>;
}

/**
 * 一覧画面の検索条件を URL と同期して持つ
 * 検索ボタンを押すまで、入力中の条件 (input) は検索条件 (params) に反映しない
 */
export const createSearchState = <P extends { page: number }>(options: SearchStateOptions<P>) => {
  const initial = options.parse(new URLSearchParams(window.location.search));
  const [params, setParams] = createSignal<P>(initial);
  const [input, setInput] = createSignal<P>(initial);

  const updateInput = (patch: Partial<P>) => setInput(current => ({ ...current, ...patch }));

  const apply = (next: P) => {
    setParams(() => next);

    const query = new URLSearchParams();
    for (const [key, value] of Object.entries(options.toQuery(next))) {
      if (value !== undefined && value !== '') query.set(key, String(value));
    }
    history.pushState(null, '', `?${query.toString()}`);
  };

  const handleSearch = (event: Event) => {
    event.preventDefault();
    // 検索時は必ず 1 ページ目に戻る
    apply({ ...input(), page: 1 });
  };

  const handleReset = () => {
    setInput(() => options.defaults);
    apply(options.defaults);
  };

  const handlePageChange = (page: number) => {
    apply({ ...params(), page });
  };

  return { params, input, updateInput, handleSearch, handleReset, handlePageChange };
};

interface ApiResult<T> {
  data: T | null;
  status: number;
}

/**
 * 一覧 API の結果を resource にする
 * 401 はログイン画面へ戻し、403 は forbiddenMessage、それ以外の失敗は共通文言を fetchError に入れる
 */
export const createSearchResource = <P, T>(
  params: Accessor<P>,
  fetcher: (params: P) => Promise<ApiResult<T>>,
  options: { forbiddenMessage?: string } = {},
) => {
  const [fetchError, setFetchError] = createSignal<string | null>(null);

  const [data, { refetch }] = createResource(params, async (current) => {
    setFetchError(null);
    const { data, status } = await fetcher(current);

    if (status === 401) {
      redirectToLogin();
      return;
    }

    if (status === 403 && options.forbiddenMessage) {
      setFetchError(options.forbiddenMessage);
      return;
    }

    if (!data) {
      setFetchError(FETCH_ERROR_MESSAGE);
      return;
    }

    return data;
  });

  return { data, refetch, fetchError };
};
