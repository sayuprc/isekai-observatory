import { siteStatsServiceGetSiteStats } from '../../generated/sdk.gen.js';
import { apiClient } from '../../shared/api/client.js';
import { requireData } from '../../shared/api/require-data.js';
import type { SiteStats } from './types.js';

async function get(): Promise<SiteStats> {
  return requireData('siteStatsServiceGetSiteStats', await siteStatsServiceGetSiteStats({ client: apiClient }));
}

export const siteStatsRepository = {
  get,
};
