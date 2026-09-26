import { songServiceListSongs } from '../../generated/sdk.gen.js';
import { apiClient } from '../../shared/api/client.js';
import { collectAll } from '../../shared/api/collect-all.js';
import { requireData } from '../../shared/api/require-data.js';
import type { Song } from './types.js';

async function all(): Promise<Song[]> {
  return collectAll(
    async (cursor, limit) => {
      const result = await songServiceListSongs({ client: apiClient, query: { limit, cursor } });
      return requireData('songServiceListSongs', result);
    },
    (data) => ({ items: data.songs, nextCursor: data.nextCursor }),
  );
}

export const songRepository = {
  all,
};
