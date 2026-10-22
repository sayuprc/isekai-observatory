import { CHUNK_SIZE } from '../shared/layout.ts';

export type ListPage = {
  items: unknown[];
  nextPageToken?: string;
};

/**
 * nextPageToken をたどって一覧の全ページを順に返す
 */
export async function* fetchAllPages(fetchPage: (pageToken: string | undefined) => Promise<ListPage>) {
  let pageToken: string | undefined;

  do {
    const page = await fetchPage(pageToken);

    yield page.items;

    pageToken = page.nextPageToken;
  } while (pageToken !== undefined);
}

/**
 * ページの区切りに関係なく CHUNK_SIZE 件ずつに詰め直す。最後の塊だけ CHUNK_SIZE 未満になる
 */
export async function* rechunk(pages: AsyncIterable<unknown[]>) {
  let buffer: unknown[] = [];

  for await (const items of pages) {
    buffer.push(...items);

    while (buffer.length >= CHUNK_SIZE) {
      yield buffer.slice(0, CHUNK_SIZE);
      buffer = buffer.slice(CHUNK_SIZE);
    }
  }

  if (buffer.length > 0) {
    yield buffer;
  }
}
