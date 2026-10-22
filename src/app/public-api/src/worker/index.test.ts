import { describe, expect, it } from 'bun:test';
import { handle } from './index.ts';
import { encodePageToken } from './page-token.ts';

const items = (from: number, to: number): number[] => Array.from({ length: to - from }, (_, i) => from + i);

/** songs を 250 件持つスナップショット */
const env = {
  ASSETS: {
    fetch: async (request: Request | string): Promise<Response> => {
      const files: Record<string, unknown> = {
        '/songs/meta.json': { count: 250 },
        '/songs/0.json': items(0, 100),
        '/songs/1.json': items(100, 200),
        '/songs/2.json': items(200, 250),
        '/venues/meta.json': { count: 0 },
      };
      const body = files[new URL(typeof request === 'string' ? request : request.url).pathname];

      return body === undefined ? new Response(null, { status: 404 }) : Response.json(body);
    },
  },
};

const get = (path: string, method = 'GET') => handle(new Request(`https://api.example.com${path}`, { method }), env);

describe('一覧の取得', () => {
  it('pageSize を省略すると先頭の 100 件と次ページの token を返す', async () => {
    const response = await get('/v1/songs');
    const body = await response.json();

    expect(response.status).toBe(200);
    expect(response.headers.get('Access-Control-Allow-Origin')).toBe('*');
    expect(body.items).toEqual(items(0, 100));
    expect(body.nextPageToken).toBe(encodePageToken(100));
  });

  it('ファイルの境界をまたぐページを 2 ファイルから切り出す', async () => {
    const response = await get(`/v1/songs?pageSize=30&pageToken=${encodePageToken(90)}`);

    expect(await response.json()).toEqual({ items: items(90, 120), nextPageToken: encodePageToken(120) });
  });

  it('nextPageToken をたどると全件を重複なく取得でき、最終ページでは token を返さない', async () => {
    const collected: unknown[] = [];
    let pageToken: string | undefined;

    do {
      const query = pageToken === undefined ? '' : `&pageToken=${pageToken}`;
      const body = await (await get(`/v1/songs?pageSize=70${query}`)).json();
      collected.push(...body.items);
      pageToken = body.nextPageToken;
    } while (pageToken !== undefined);

    expect(collected).toEqual(items(0, 250));
  });

  it('0 件の一覧は空の items だけを返す', async () => {
    expect(await (await get('/v1/venues')).json()).toEqual({ items: [] });
  });
});

describe('不正なリクエスト', () => {
  it.each(['0', '101', '1.5', 'abc'])('pageSize=%s は 400 を返す', async (pageSize) => {
    const response = await get(`/v1/songs?pageSize=${pageSize}`);

    expect(response.status).toBe(400);
    expect((await response.json()).code).toBe('business_rule_violation');
  });

  it.each([
    ['読めない token', '***'],
    ['数値でない token', btoa('abc')],
    ['件数を超える token', encodePageToken(250)],
  ])('%s は 400 を返す', async (_, pageToken) => {
    expect((await get(`/v1/songs?pageToken=${encodeURIComponent(pageToken)}`)).status).toBe(400);
  });

  it('公開していない path は 404 を返す', async () => {
    expect((await get('/v1/song-tags')).status).toBe(404);
    expect((await get('/songs/meta.json')).status).toBe(404);
  });

  it('GET 以外は 405 を返し、OPTIONS は CORS の preflight に応える', async () => {
    expect((await get('/v1/songs', 'POST')).status).toBe(405);

    const preflight = await get('/v1/songs', 'OPTIONS');
    expect(preflight.status).toBe(204);
    expect(preflight.headers.get('Access-Control-Allow-Origin')).toBe('*');
  });
});
