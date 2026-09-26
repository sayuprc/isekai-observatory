import { releaseGroupServiceListReleaseGroups } from '../../generated/sdk.gen.js';
import { apiClient } from '../../shared/api/client.js';
import { collectAll } from '../../shared/api/collect-all.js';
import { requireData } from '../../shared/api/require-data.js';
import type { ReleaseGroup } from './types.js';

async function all(): Promise<ReleaseGroup[]> {
  return collectAll(
    async (cursor, limit) => {
      const result = await releaseGroupServiceListReleaseGroups({ client: apiClient, query: { limit, cursor } });
      return requireData('releaseGroupServiceListReleaseGroups', result);
    },
    data => ({ items: data.releaseGroups, nextCursor: data.nextCursor }),
  );
}

export const releaseGroupRepository = {
  all,
};
