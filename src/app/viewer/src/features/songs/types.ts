import type { SongListItem, SongMediaSummary, SongTypeValue } from '../../generated/types.gen.js';

export type Song = SongListItem;

export type SongMedia = SongMediaSummary;

export const SONG_TYPE_NAMES: Record<SongTypeValue, string> = {
  1: 'オリジナル曲',
  2: 'カバー曲',
};
