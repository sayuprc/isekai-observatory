import type { EventRelease } from '../../generated';
import { client } from '../../utils/client';

// 候補は版の単位なので、リリースグループではなくリリースの検索 API を使う
export const searchEventReleases = async (
  title: string,
): Promise<{ items: EventRelease[] | undefined; status: number }> => {
  const { data, status } = await client.api.releases.search.get({
    query: { title, page: 1, per_page: 25 },
  });

  return { items: data?.releases, status };
};
