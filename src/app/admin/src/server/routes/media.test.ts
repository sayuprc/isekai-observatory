import { beforeEach, describe, expect, it, mock } from 'bun:test';

const credentials: Record<string, unknown> = {};
const createCalls: unknown[] = [];
const updateCalls: unknown[] = [];

mock.module('astro:env/server', () => ({
  CACHE_TOKEN: 'cache-token',
  CACHE_URL: 'https://cache.local',
}));

mock.module('@upstash/redis', () => {
  class Redis {
    async set(key: string, value: unknown): Promise<'OK'> {
      credentials[key] = value;
      return 'OK';
    }

    async get<T>(key: string): Promise<T | null> {
      return (credentials[key] as T | undefined) ?? null;
    }

    async del(key: string): Promise<number> {
      delete credentials[key];
      return 1;
    }
  }
  return { Redis };
});

mock.module('../client', () => ({
  withAuthRetry: async <T>(_authSession: unknown, callback: (client: unknown) => Promise<T>): Promise<T> => {
    return callback({ kind: 'auth-client' });
  },
}));

mock.module('../../generated', () => ({
  mediaServiceCreateMedia: async ({ body }: { body: unknown }) => {
    createCalls.push(body);

    return {
      data: { media: { mediaId: 'media-id' } },
      response: new Response(null, { status: 201 }),
    };
  },
  mediaServiceDeleteMedia: async () => ({
    data: {},
    response: new Response(null, { status: 200 }),
  }),
  mediaServiceGetMedia: async () => ({
    data: {},
    response: new Response(null, { status: 200 }),
  }),
  mediaServiceSearchMedia: async () => ({
    data: { media: [], maxPage: 1 },
    response: new Response(null, { status: 200 }),
  }),
  mediaServiceUpdateMedia: async ({ body }: { body: unknown }) => {
    updateCalls.push(body);

    return {
      data: { media: { mediaId: 'media-id' } },
      response: new Response(null, { status: 200 }),
    };
  },
}));

const { media } = await import('./media');

describe('media routes', () => {
  beforeEach(() => {
    for (const key of Object.keys(credentials)) {
      delete credentials[key];
    }
    createCalls.length = 0;
    updateCalls.length = 0;
    credentials['session:test-session'] = {
      accessToken: 'access-token',
      refreshTokenId: 'refresh-token-id',
      refreshToken: 'refresh-token',
      csrfToken: 'csrf-token',
    };
  });

  it('datetime-local の公開日時を API 契約の date-time 形式に正規化して作成 API を呼ぶ', async () => {
    const response = await media.handle(
      new Request('http://localhost/media/', {
        method: 'POST',
        headers: {
          'content-type': 'application/json',
          'cookie': 'session=test-session',
          'x-csrf-token': 'csrf-token',
        },
        body: JSON.stringify({
          title: 'media title',
          url: 'https://example.com/media',
          publishedAt: '2024-03-01T12:34:56',
          typeValue: 1,
          isDisplay: true,
        }),
      }),
    );

    expect(response.status).toBe(200);
    expect(createCalls).toHaveLength(1);
    expect(createCalls[0]).toMatchObject({
      publishedAt: '2024-03-01T12:34:56+09:00',
    });
  });

  it('datetime-local の公開日時を API 契約の date-time 形式に正規化して更新 API を呼ぶ', async () => {
    const response = await media.handle(
      new Request('http://localhost/media/media-id', {
        method: 'PUT',
        headers: {
          'content-type': 'application/json',
          'cookie': 'session=test-session',
          'x-csrf-token': 'csrf-token',
        },
        body: JSON.stringify({
          title: 'media title',
          url: 'https://example.com/media',
          publishedAt: '2024-03-01T12:34',
          typeValue: 1,
          isDisplay: true,
        }),
      }),
    );

    expect(response.status).toBe(200);
    expect(updateCalls).toHaveLength(1);
    expect(updateCalls[0]).toMatchObject({
      publishedAt: '2024-03-01T12:34:00+09:00',
    });
  });
});
