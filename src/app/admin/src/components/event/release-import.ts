import type { ReleaseGetResponse } from '../../generated';
import type { SongCandidate } from '../song-import';
import { addPerformance, newId, type PerformanceForm, type SetlistItemForm } from './performance-form';

/**
 * リリースの媒体・曲順どおりに、1 トラック 1 候補で並べる。メドレーは 1 つの候補のまま保つ
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
      const label = track.title ?? '';

      if (track.songIds.length === 0) {
        return {
          key,
          songs: [],
          label,
          note: `${note} (管理対象外楽曲)`,
          selectable: false,
          defaultSelected: false,
        };
      }

      const songs = track.songIds.map((songId) => ({ songId, title: titleBySongId.get(songId) ?? songId }));
      // 1 曲のトラック名は楽曲名の表記違い、メドレーのトラック名はメドレー名として補足に出す
      const named = label !== '' && (songs.length > 1 || label !== songs[0]?.title);

      return {
        key,
        songs,
        label,
        note: named ? `${note} (${songs.length > 1 ? label : `表記: ${label}`})` : note,
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
    candidate.songs.length === 0 && candidate.label !== ''
      ? { ...candidate, selectable: true, defaultSelected: true }
      : candidate,
  );

// 同じ楽曲の複数回披露もありうるので、既存の披露と重複していても追加する。メドレーは楽曲ごとに披露を作る
export const addPerformancesFromCandidates = (
  performances: PerformanceForm[],
  candidates: SongCandidate[],
): PerformanceForm[] =>
  candidates
    .flatMap((candidate) => candidate.songs)
    .reduce((acc, song) => addPerformance(acc, { songId: song.songId, title: song.title }), performances);

/**
 * 候補を 1 件 1 項目でセットリスト末尾に足す
 * 楽曲を参照する候補は楽曲ごとに楽曲披露も末尾に足して項目に紐づけ、参照しない候補は表示名だけの項目にする
 * 1 曲のトラック名は楽曲名の表記違いなので、項目の表示名にするのはメドレー名だけにする
 */
export const addSetlistFromCandidates = (
  current: { performances: PerformanceForm[]; setlist: SetlistItemForm[] },
  candidates: SongCandidate[],
): { performances: PerformanceForm[]; setlist: SetlistItemForm[] } =>
  candidates.reduce(({ performances, setlist }, candidate) => {
    if (candidate.songs.length === 0) {
      return {
        performances,
        setlist: [...setlist, { setlistItemId: newId(), label: candidate.label, performanceIds: [] }],
      };
    }

    const added = candidate.songs.map((song) => ({
      performanceId: newId(),
      songId: song.songId,
      songTitle: song.title,
      coVocalists: [],
    }));

    return {
      performances: [...performances, ...added],
      setlist: [
        ...setlist,
        {
          setlistItemId: newId(),
          label: candidate.songs.length > 1 ? candidate.label : '',
          performanceIds: added.map((performance) => performance.performanceId),
        },
      ],
    };
  }, current);
