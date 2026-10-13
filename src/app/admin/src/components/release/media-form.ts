import type { ReleaseGetResponse } from '../../generated';

export type TrackSongForm = {
  songId: string;
  title: string;
};

/**
 * songs が空のトラックは管理対象外楽曲(タイトルのみトラック)で title が必須
 * 楽曲 1 件のトラックは title が空なら楽曲名で表示、入力があれば上書き名になる
 * 楽曲 2 件以上のトラックはメドレーで、title はメドレーの表示名になる
 */
export type TrackForm = {
  songs: TrackSongForm[];
  title: string;
};

export type MediumForm = {
  name: string;
  tracks: TrackForm[];
};

/** API レスポンスからフォーム状態を組み立てる(楽曲名は収録曲 read model から引く) */
export const toMediumForms = (data: ReleaseGetResponse): MediumForm[] => {
  const titleBySongId = new Map(
    data.songs.flatMap((song) => (song.songId !== null ? [[song.songId, song.title] as const] : [])),
  );

  return data.release.media.map((medium) => ({
    name: medium.name ?? '',
    tracks: medium.tracks.map((track) => ({
      songs: track.songIds.map((songId) => ({ songId, title: titleBySongId.get(songId) ?? songId })),
      title: track.title ?? '',
    })),
  }));
};

/** フォーム状態を API の media リクエスト形へ変換する(position / trackNo は並び順から採番、空の title / name は null) */
export const toMediaPayload = (media: MediumForm[]) =>
  media.map((medium, mediumIndex) => ({
    position: mediumIndex + 1,
    name: medium.name.trim() === '' ? null : medium.name.trim(),
    tracks: medium.tracks.map((track, trackIndex) => ({
      songIds: track.songs.map((song) => song.songId),
      title: track.title.trim() === '' ? null : track.title.trim(),
      trackNo: trackIndex + 1,
    })),
  }));

/**
 * 直前のトラックの末尾へ楽曲を移してメドレーにする
 * タイトルのみトラックはタイトルが失われるので、どちらかが楽曲を持たなければまとめない
 */
export const canMergeWithPrevious = (tracks: TrackForm[], index: number): boolean =>
  index > 0 && (tracks[index - 1]?.songs.length ?? 0) > 0 && (tracks[index]?.songs.length ?? 0) > 0;

export const mergeWithPrevious = (tracks: TrackForm[], index: number): TrackForm[] => {
  if (!canMergeWithPrevious(tracks, index)) {
    return tracks;
  }

  const previous = tracks[index - 1]!;
  const current = tracks[index]!;
  // 楽曲 1 件のトラック名は楽曲名の上書きでメドレー名ではないので、メドレー名として引き継ぐのはメドレー側の名前だけにする
  const title = [previous, current].find((track) => track.songs.length > 1 && track.title.trim() !== '')?.title ?? '';

  return tracks.flatMap((track, i) => {
    if (i === index - 1) {
      return [{ songs: [...previous.songs, ...current.songs], title }];
    }
    return i === index ? [] : [track];
  });
};

/**
 * メドレーから楽曲を外し、メドレーの直後に 1 曲のトラックとして置く
 * 外した結果 1 曲だけになったトラックは、メドレー名が楽曲名の上書きにならないよう名前を空にする
 */
export const splitFromMedley = (tracks: TrackForm[], index: number, songIndex: number): TrackForm[] => {
  const medley = tracks[index];
  const song = medley?.songs[songIndex];

  if (!medley || !song || medley.songs.length < 2) {
    return tracks;
  }

  const rest = medley.songs.filter((_, i) => i !== songIndex);
  const remaining = { songs: rest, title: rest.length > 1 ? medley.title : '' };

  return tracks.flatMap((track, i) => (i === index ? [remaining, { songs: [song], title: '' }] : [track]));
};
