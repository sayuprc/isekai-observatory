import type { ReleaseGetResponse } from '../../generated';
import type { SongCandidate } from '../song-import';
import { addPerformance, newId, type PerformanceForm, type SetlistItemForm } from './performance-form';

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

/**
 * セットリストへ取り込むときは、タイトルのみトラックも表示名だけの項目として選べるようにする
 * 表示名が空の項目は保存できないので、タイトルのないトラックは選べないままにする
 */
export const toReleaseSetlistCandidates = (data: Pick<ReleaseGetResponse, 'release' | 'songs'>): SongCandidate[] =>
  toReleasePerformanceCandidates(data).map((candidate) =>
    candidate.songId === null && candidate.title !== ''
      ? { ...candidate, selectable: true, defaultSelected: true }
      : candidate,
  );

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

/**
 * 候補を 1 件 1 項目でセットリスト末尾に足す
 * 楽曲を参照する候補は楽曲披露も末尾に足して項目に紐づけ、参照しない候補は表示名だけの項目にする
 */
export const addSetlistFromCandidates = (
  current: { performances: PerformanceForm[]; setlist: SetlistItemForm[] },
  candidates: SongCandidate[],
): { performances: PerformanceForm[]; setlist: SetlistItemForm[] } =>
  candidates.reduce(({ performances, setlist }, candidate) => {
    if (candidate.songId === null) {
      return {
        performances,
        setlist: [...setlist, { setlistItemId: newId(), label: candidate.title, performanceIds: [] }],
      };
    }

    const performanceId = newId();

    return {
      performances: [
        ...performances,
        { performanceId, songId: candidate.songId, songTitle: candidate.title, coVocalists: [] },
      ],
      setlist: [...setlist, { setlistItemId: newId(), label: '', performanceIds: [performanceId] }],
    };
  }, current);
