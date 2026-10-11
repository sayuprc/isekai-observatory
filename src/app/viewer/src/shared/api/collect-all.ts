import { LIST_PAGE_LIMIT } from './list-limit.js';

export type PaginatedPage<T> = {
  items: T[];
  nextCursor?: string;
};

/**
 * カーソル付き一覧 API を全件取得する
 * `pick` は成功レスポンスから items と nextCursor を取り出す
 */
export async function collectAll<T, R>(
  fetchPage: (cursor: string | undefined, limit: number) => Promise<R>,
  pick: (response: R) => PaginatedPage<T>,
): Promise<T[]> {
  const items: T[] = [];
  let cursor: string | undefined;

  while (true) {
    const response = await fetchPage(cursor, LIST_PAGE_LIMIT);
    const page = pick(response);

    items.push(...page.items);

    if (!page.nextCursor) {
      break;
    }
    cursor = page.nextCursor;
  }

  return items;
}
