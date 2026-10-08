import type { MediaListItem, MediaSongSummary, MediaTypeValue } from '../../generated/types.gen.js';

export type Media = MediaListItem;

export type MediaSong = MediaSongSummary;

export const MEDIA_TYPE_NAMES: Record<MediaTypeValue, string> = {
  1: 'MV',
  2: '音源動画',
  3: '配信',
  4: 'ショート',
  5: '投稿',
  99: 'その他',
};
