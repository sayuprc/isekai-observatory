import { songServiceListSongs } from '../../generated/sdk.gen.js';
import { apiClient } from '../../shared/api/client.js';
import { collectAll } from '../../shared/api/collect-all.js';
import type { Song } from './types.js';

async function all(): Promise<Song[]> {
  return collectAll(
    async (cursor, limit) => {
      const { data, error, response } = await songServiceListSongs({
        client: apiClient,
        query: { limit, cursor },
      });

      if (!data) {
        throw new Error(`songServiceListSongs failed: HTTP ${response.status} ${JSON.stringify(error)}`);
      }

      return data;
    },
    data => ({ items: data.songs, nextCursor: data.nextCursor }),
  );
}

export const songRepository = {
  all,
};
