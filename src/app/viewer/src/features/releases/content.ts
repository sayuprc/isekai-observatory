import { getCollection } from 'astro:content';
import { allFromCollection, latestFromCollection, sortByIndex, stripIndex } from '../../shared/content/collection';
import type { ReleaseGroupCollectionItem } from './schema';
import type { ReleaseGroup } from './types';

async function entries() {
  return sortByIndex<ReleaseGroupCollectionItem>(await getCollection('releaseGroups'));
}

type ReleaseGroupCollectionEntry = Awaited<ReturnType<typeof entries>>[number];

function fromEntry(entry: ReleaseGroupCollectionEntry): ReleaseGroup {
  return stripIndex(entry.data);
}

async function all(): Promise<ReleaseGroup[]> {
  return allFromCollection<ReleaseGroupCollectionItem>(await getCollection('releaseGroups'));
}

async function latest(): Promise<ReleaseGroup | null> {
  return latestFromCollection<ReleaseGroupCollectionItem>(await getCollection('releaseGroups'), 'first');
}

export const releaseGroupContentRepository = {
  all,
  entries,
  fromEntry,
  latest,
};
