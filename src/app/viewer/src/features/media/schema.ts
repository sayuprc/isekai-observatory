import { z } from 'astro:content';
import type { Media } from './types';

export type MediaCollectionItem = Media & {
  index: number;
};

export const mediaCollectionItemSchema = z.custom<MediaCollectionItem>(
  (val: unknown) => typeof val === 'object' && val !== null && typeof (val as MediaCollectionItem).index === 'number',
);
