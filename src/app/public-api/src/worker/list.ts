import type { Meta, Resource } from '../shared/layout.ts';
import { CHUNK_SIZE, chunkPath, metaPath } from '../shared/layout.ts';
import { decodePageToken, encodePageToken } from './page-token.ts';

export const DEFAULT_PAGE_SIZE = 100;

const MAX_PAGE_SIZE = 100;

/** 静的ファイルを path で読む。Worker では ASSETS binding が担う */
export type ReadJson = (path: string) => Promise<unknown>;

export type ListResult =
  { ok: true; body: { items: unknown[]; nextPageToken?: string } } | { ok: false; message: string };

const parsePageSize = (value: string | null): number | null => {
  if (value === null) {
    return DEFAULT_PAGE_SIZE;
  }

  if (!/^\d+$/.test(value)) {
    return null;
  }

  const pageSize = Number(value);

  return pageSize >= 1 && pageSize <= MAX_PAGE_SIZE ? pageSize : null;
};

/**
 * 一覧の 1 ページを、100 件ずつの静的ファイルから切り出す
 * 1 ページは最大 100 件なので、読むファイルは最大 2 つになる
 */
export const listPage = async (
  resource: Resource,
  params: URLSearchParams,
  readJson: ReadJson,
): Promise<ListResult> => {
  const pageSize = parsePageSize(params.get('pageSize'));

  if (pageSize === null) {
    return { ok: false, message: 'pageSize は 1 から 100 の整数で指定してください。' };
  }

  const { count } = (await readJson(metaPath(resource))) as Meta;
  const pageToken = params.get('pageToken');
  const offset = pageToken === null ? 0 : decodePageToken(pageToken);

  // deploy でページ数が減ったときの token も範囲外として扱う
  if (offset === null || (pageToken !== null && offset >= count)) {
    return { ok: false, message: 'pageToken が不正です。' };
  }

  const end = Math.min(offset + pageSize, count);
  const firstChunk = Math.floor(offset / CHUNK_SIZE);
  const lastChunk = Math.floor((end - 1) / CHUNK_SIZE);
  const chunks =
    end > offset
      ? await Promise.all(
          Array.from(
            { length: lastChunk - firstChunk + 1 },
            (_, i) => readJson(chunkPath(resource, firstChunk + i)) as Promise<unknown[]>,
          ),
        )
      : [];
  const start = offset - firstChunk * CHUNK_SIZE;
  const items = chunks.flat().slice(start, start + (end - offset));

  return {
    ok: true,
    body: end < count ? { items, nextPageToken: encodePageToken(end) } : { items },
  };
};
