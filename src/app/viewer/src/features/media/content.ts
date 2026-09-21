import { getCollection } from 'astro:content';
import { allFromCollection } from '../../shared/content/collection';
import type { MediaCollectionItem } from './schema';
import type { Media } from './types';

async function all(): Promise<Media[]> {
  return allFromCollection<MediaCollectionItem>(await getCollection('media'));
}

export const mediaContentRepository = {
  all,
};
