import type { ReleaseGetResponse } from '../../generated';
import type { SongCandidate } from '../song-import';
import { addPerformance, type PerformanceForm } from './performance-form';

/**
 * リリースの媒体・曲順どおりに、楽曲披露の候補を並べる
 * 楽曲披露は必ず楽曲を参照するので、タイトルのみトラックは選べない候補として見せるだけにする
 */
export const toReleasePerformanceCandidates = (
  data: Pick<ReleaseGetResponse, 'release' | 'songs'>,
): SongCandidate[] => {
  const titleBySongId = new Map(
    data.songs.flatMap((song) => (song.songId !== null ? [[song.songId, song.title] as const] : [])),
  );

  return data.release.media.flatMap((medium) =>
    medium.tracks.map((track) => {
      const mediumName = medium.name ?? `媒体 ${medium.position}`;
      const note = `${mediumName} - ${track.trackNo}`;
      const key = `${medium.position}-${track.trackNo}`;

      if (track.songId === null) {
        return {
          key,
          songId: null,
          title: track.title ?? '',
          note: `${note} (管理対象外楽曲)`,
          selectable: false,
          defaultSelected: false,
        };
      }

      const songTitle = titleBySongId.get(track.songId) ?? track.songId;

      return {
        key,
        songId: track.songId,
        title: songTitle,
        note: track.title !== null && track.title !== songTitle ? `${note} (表記: ${track.title})` : note,
        selectable: true,
        defaultSelected: true,
      };
    }),
  );
};

// 同じ楽曲の複数回披露もありうるので、既存の披露と重複していても追加する
export const addPerformancesFromCandidates = (
  performances: PerformanceForm[],
  candidates: SongCandidate[],
): PerformanceForm[] =>
  candidates.reduce(
    (acc, candidate) =>
      candidate.songId === null ? acc : addPerformance(acc, { songId: candidate.songId, title: candidate.title }),
    performances,
  );
