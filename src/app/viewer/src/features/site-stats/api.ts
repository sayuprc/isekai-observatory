import { siteStatsServiceGetSiteStats } from '../../generated/sdk.gen.js';
import { apiClient } from '../../shared/api/client.js';
import type { SiteStats } from './types.js';

async function get(): Promise<SiteStats> {
  const { data, error, response } = await siteStatsServiceGetSiteStats({
    client: apiClient,
  });

  if (!data) {
    throw new Error(`siteStatsServiceGetSiteStats failed: HTTP ${response.status} ${JSON.stringify(error)}`);
  }

  return data;
}

export const siteStatsRepository = {
  get,
};
