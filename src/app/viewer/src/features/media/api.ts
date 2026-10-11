import { mediaServiceListMedia } from '../../generated/sdk.gen.js';
import { apiClient } from '../../shared/api/client.js';
import { collectAll } from '../../shared/api/collect-all.js';
import { requireData } from '../../shared/api/require-data.js';
import type { Media } from './types.js';

async function all(): Promise<Media[]> {
  return collectAll(
    async (cursor, limit) => {
      const result = await mediaServiceListMedia({ client: apiClient, query: { limit, cursor } });
      return requireData('mediaServiceListMedia', result);
    },
    (data) => ({ items: data.media, nextCursor: data.nextCursor }),
  );
}

export const mediaRepository = {
  all,
};
