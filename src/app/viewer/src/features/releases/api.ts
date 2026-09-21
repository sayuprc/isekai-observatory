import { releaseGroupServiceListReleaseGroups } from '../../generated/sdk.gen.js';
import { apiClient } from '../../shared/api/client.js';
import { collectAll } from '../../shared/api/collect-all.js';
import type { ReleaseGroup } from './types.js';

async function all(): Promise<ReleaseGroup[]> {
  return collectAll(
    async (cursor, limit) => {
      const { data, error, response } = await releaseGroupServiceListReleaseGroups({
        client: apiClient,
        query: { limit, cursor },
      });

      if (!data) {
        throw new Error(`releaseGroupServiceListReleaseGroups failed: HTTP ${response.status} ${JSON.stringify(error)}`);
      }

      return data;
    },
    data => ({ items: data.releaseGroups, nextCursor: data.nextCursor }),
  );
}

export const releaseGroupRepository = {
  all,
};
