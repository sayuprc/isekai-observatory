import { z } from 'astro:content';
import type { ReleaseGroup } from './types';

export type ReleaseGroupCollectionItem = ReleaseGroup & {
  index: number;
};

export const releaseGroupCollectionItemSchema = z.custom<ReleaseGroupCollectionItem>(
  (val: unknown) => typeof val === 'object' && val !== null && typeof (val as ReleaseGroupCollectionItem).index === 'number',
);
