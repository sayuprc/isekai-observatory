import { createSignal } from 'solid-js';

interface KeywordSearchOptions<T> {
  emptyKeywordMessage: string;
  fetch: (keyword: string) => Promise<{ items: T[] | undefined; status: number }>;
}

export const createKeywordSearch = <T>(options: KeywordSearchOptions<T>) => {
  const [keyword, setKeyword] = createSignal('');
  const [results, setResults] = createSignal<T[]>([]);
  const [error, setError] = createSignal<string | null>(null);
  const [isSearching, setIsSearching] = createSignal(false);
  const [hasSearched, setHasSearched] = createSignal(false);

  const search = async (event: Event) => {
    event.preventDefault();
    setError(null);
    setHasSearched(true);

    const trimmed = keyword().trim();
    if (trimmed === '') {
      setResults([]);
      setError(options.emptyKeywordMessage);
      return;
    }

    setIsSearching(true);
    const { items, status } = await options.fetch(trimmed);
    setIsSearching(false);

    if (status === 401) {
      window.location.href = '/auth/login';
      return;
    }

    if (!items) {
      setError(`検索に失敗しました (${status})`);
      setResults([]);
      return;
    }

    setResults(items);
  };

  return { keyword, setKeyword, results, error, isSearching, hasSearched, search };
};

export type KeywordSearch<T> = ReturnType<typeof createKeywordSearch<T>>;
