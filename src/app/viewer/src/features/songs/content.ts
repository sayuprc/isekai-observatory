import { getCollection } from 'astro:content';
import { allFromCollection, latestFromCollection } from '../../shared/content/collection';
import type { SongCollectionItem } from './schema';
import type { Song } from './types';

async function all(): Promise<Song[]> {
  return allFromCollection<SongCollectionItem>(await getCollection('songs'));
}

async function latest(): Promise<Song | null> {
  return latestFromCollection<SongCollectionItem>(await getCollection('songs'), 'first');
}

export const songContentRepository = {
  all,
  latest,
};
