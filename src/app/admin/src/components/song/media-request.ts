import type { RequestSongMediaLink } from '../../generated';
import type { MediaEntry } from '../media/MediaSection';

export const buildSongMediaRequest = (entries: MediaEntry[]): RequestSongMediaLink[] =>
  entries.map((entry, index) => ({
    mediaId: entry.mediaId,
    orderNo: index + 1,
  }));
