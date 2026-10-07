import type {
  SongListItem,
  SongMediaSummary,
  SongReleaseGroupSummary,
  SongTypeValue,
} from '../../generated/types.gen.js';

export type Song = SongListItem;

export type SongMedia = SongMediaSummary;

export type SongReleaseGroup = SongReleaseGroupSummary;

export const SONG_TYPE_NAMES: Record<SongTypeValue, string> = {
  1: 'オリジナル曲',
  2: 'カバー曲',
};
