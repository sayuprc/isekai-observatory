import { z } from 'astro:content';
import type { Song } from './types';

export type SongCollectionItem = Song & {
  index: number;
};

export const songCollectionItemSchema = z.custom<SongCollectionItem>(
  (val: unknown) => typeof val === 'object' && val !== null && typeof (val as SongCollectionItem).index === 'number',
);
