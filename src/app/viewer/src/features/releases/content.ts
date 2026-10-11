import { getCollection } from 'astro:content';
import { allFromCollection, latestFromCollection, sortByIndex, stripIndex } from '../../shared/content/collection';
import type { Indexed } from '../../shared/content/indexed';
import type { ReleaseGroup } from './types';

async function entries() {
  return sortByIndex<Indexed<ReleaseGroup>>(await getCollection('releaseGroups'));
}

type ReleaseGroupCollectionEntry = Awaited<ReturnType<typeof entries>>[number];

function fromEntry(entry: ReleaseGroupCollectionEntry): ReleaseGroup {
  return stripIndex(entry.data);
}

async function all(): Promise<ReleaseGroup[]> {
  return allFromCollection<Indexed<ReleaseGroup>>(await getCollection('releaseGroups'));
}

async function latest(): Promise<ReleaseGroup | null> {
  return latestFromCollection<Indexed<ReleaseGroup>>(await getCollection('releaseGroups'), 'first');
}

export const releaseGroupContentRepository = {
  all,
  entries,
  fromEntry,
  latest,
};
