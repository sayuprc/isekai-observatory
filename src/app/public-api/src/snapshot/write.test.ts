import { describe, expect, it } from 'bun:test';
import type { ListPage } from './pages.ts';
import { fetchAllPages } from './pages.ts';
import { writeResource } from './write.ts';

const items = (from: number, to: number): number[] => Array.from({ length: to - from }, (_, i) => from + i);

const pagesOf = (...pages: number[][]): ((pageToken: string | undefined) => Promise<ListPage>) => {
  return async (pageToken) => {
    const index = pageToken === undefined ? 0 : Number(pageToken);

    return {
      items: pages[index] ?? [],
      ...(index + 1 < pages.length ? { nextPageToken: String(index + 1) } : {}),
    };
  };
};

const writeToMap = async (pages: number[][]) => {
  const files = new Map<string, unknown>();
  const meta = await writeResource('songs', fetchAllPages(pagesOf(...pages)), async (path, content) => {
    files.set(path, JSON.parse(content));
  });

  return { files, meta };
};

describe('writeResource', () => {
  it('nextPageToken をたどった全件を 100 件ずつのファイルに分け、件数をメタデータに書く', async () => {
    const { files, meta } = await writeToMap([items(0, 100), items(100, 200), items(200, 250)]);

    expect(meta).toEqual({ count: 250 });
    expect(files.get('/songs/meta.json')).toEqual({ count: 250 });
    expect(files.get('/songs/0.json')).toEqual(items(0, 100));
    expect(files.get('/songs/1.json')).toEqual(items(100, 200));
    expect(files.get('/songs/2.json')).toEqual(items(200, 250));
    expect(files.size).toBe(4);
  });

  it('取得元のページの区切りが 100 件でなくても 100 件ずつに詰め直す', async () => {
    const { files } = await writeToMap([items(0, 30), items(30, 130), items(130, 150)]);

    expect(files.get('/songs/0.json')).toEqual(items(0, 100));
    expect(files.get('/songs/1.json')).toEqual(items(100, 150));
  });

  it('0 件のときはメタデータだけを書く', async () => {
    const { files, meta } = await writeToMap([[]]);

    expect(meta).toEqual({ count: 0 });
    expect([...files.keys()]).toEqual(['/songs/meta.json']);
  });
});
