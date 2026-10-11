import type { EventRelease } from '../../generated';
import { client } from '../../utils/client';
import { toEventReleases } from './event-links';

// 検索の主語はリリースグループなので、ヒットしたグループの版を展開して候補にする
export const searchEventReleases = async (
  title: string,
): Promise<{ items: EventRelease[] | undefined; status: number }> => {
  const { data, status } = await client.api['release-groups'].search.get({
    query: { title, sort: 'first_released_on', order: 'desc', page: 1, per_page: 25 },
  });
  if (!data) {
    return { items: undefined, status };
  }

  const details = await Promise.all(
    data.releaseGroups.map(async (releaseGroup) => {
      const detail = await client.api['release-groups']({ releaseGroupId: releaseGroup.releaseGroupId }).get();
      return { releaseGroup, detail };
    }),
  );
  const failed = details.find(({ detail }) => !detail.data);
  if (failed) {
    return { items: undefined, status: failed.detail.status };
  }

  return {
    items: details.flatMap(({ releaseGroup, detail }) => toEventReleases(releaseGroup, detail.data?.releases ?? [])),
    status,
  };
};
