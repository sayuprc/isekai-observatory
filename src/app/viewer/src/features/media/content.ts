import { getCollection } from 'astro:content';
import { allFromCollection } from '../../shared/content/collection';
import type { Indexed } from '../../shared/content/indexed';
import type { Media } from './types';

async function all(): Promise<Media[]> {
  return allFromCollection<Indexed<Media>>(await getCollection('media'));
}

export const mediaContentRepository = {
  all,
};
