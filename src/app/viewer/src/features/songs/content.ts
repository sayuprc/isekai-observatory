import { getCollection } from 'astro:content';
import { allFromCollection, latestFromCollection } from '../../shared/content/collection';
import type { Indexed } from '../../shared/content/indexed';
import type { Song } from './types';

async function all(): Promise<Song[]> {
  return allFromCollection<Indexed<Song>>(await getCollection('songs'));
}

async function latest(): Promise<Song | null> {
  return latestFromCollection<Indexed<Song>>(await getCollection('songs'), 'first');
}

export const songContentRepository = {
  all,
  latest,
};
