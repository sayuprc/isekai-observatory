import { mediaServiceListMedia } from '../../generated/sdk.gen.js';
import { apiClient } from '../../shared/api/client.js';
import { collectAll } from '../../shared/api/collect-all.js';
import type { Media } from './types.js';

async function all(): Promise<Media[]> {
  return collectAll(
    async (cursor, limit) => {
      const { data, error, response } = await mediaServiceListMedia({
        client: apiClient,
        query: { limit, cursor },
      });

      if (!data) {
        throw new Error(`mediaServiceListMedia failed: HTTP ${response.status} ${JSON.stringify(error)}`);
      }

      return data;
    },
    data => ({ items: data.media, nextCursor: data.nextCursor }),
  );
}

export const mediaRepository = {
  all,
};
