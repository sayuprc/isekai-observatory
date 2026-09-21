import { beforeEach, describe, expect, it, mock } from 'bun:test';

const credentials: Record<string, unknown> = {};
let listPersonsCalls = 0;

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

const ok = (data: unknown) => ({ data, response: new Response(null, { status: 200 }) });

mock.module('../../generated', () => ({
  mediaServiceSearchMedia: async () => ok({ media: [], maxPage: 1 }),
  personServiceListPersons: async () => {
    listPersonsCalls += 1;
    return ok({ persons: [{ personId: 'person-id', name: '人物', orderNo: 1 }] });
  },
  songServiceCreateSong: async () => ok({}),
  songServiceDeleteSong: async () => ok({}),
  songServiceGetSong: async () => ok({ song: { songId: 'song-id', persons: [], tags: [], media: [] } }),
  songServiceSearchSongs: async () => ok({ songs: [], maxPage: 1 }),
  songServiceUpdateSong: async () => ok({}),
  songTagServiceListSongTags: async () => ok({ tags: [] }),
  songTypeServiceListSongTypes: async () => ok({ types: [] }),
}));

const { songs } = await import('./songs');

describe('song form routes', () => {
  beforeEach(() => {
    for (const key of Object.keys(credentials)) {
      delete credentials[key];
    }

    listPersonsCalls = 0;
    credentials['session:test-session'] = {
      accessToken: 'access-token',
      refreshTokenId: 'refresh-token-id',
      refreshToken: 'refresh-token',
      csrfToken: 'csrf-token',
    };
  });

  it('作成フォーム表示時に全人物を事前取得しない', async () => {
    const response = await songs.handle(authenticatedRequest('http://localhost/songs/create-form'));
    const body = await response.json();

    expect(response.status).toBe(200);
    expect(listPersonsCalls).toBe(0);
    expect(body).not.toHaveProperty('persons');
  });

  it('編集フォーム表示時に全人物を事前取得しない', async () => {
    const response = await songs.handle(authenticatedRequest('http://localhost/songs/song-id/edit-form'));
    const body = await response.json();

    expect(response.status).toBe(200);
    expect(listPersonsCalls).toBe(0);
    expect(body).not.toHaveProperty('persons');
  });
});

const authenticatedRequest = (url: string) => new Request(url, {
  headers: {
    'cookie': 'session=test-session',
    'x-csrf-token': 'csrf-token',
  },
});
