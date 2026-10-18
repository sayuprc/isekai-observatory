import type {
  ReleaseFormatValue,
  ReleaseGroupListItem,
  ReleaseGroupTypeValue,
  ReleaseListItem,
  ReleaseMediumItem,
  ReleaseTrackItem,
  ReleaseTrackSong,
} from '../../generated/types.gen.js';

export type ReleaseGroup = ReleaseGroupListItem;

export type Release = ReleaseListItem;

export type ReleaseMedium = ReleaseMediumItem;

export type ReleaseTrack = ReleaseTrackItem;

export const RELEASE_GROUP_TYPE_NAMES: Record<ReleaseGroupTypeValue, string> = {
  1: 'シングル',
  2: 'アルバム',
  3: 'EP',
  99: 'その他',
};

/** 絞り込みでの並び。アルバムを先頭に出す */
export const RELEASE_GROUP_TYPE_FILTER_ORDER: readonly ReleaseGroupTypeValue[] = [2, 1, 3, 99];

const RELEASE_FORMAT_NAMES: Record<ReleaseFormatValue, string> = {
  1: '配信',
  2: 'CD',
  3: 'DVD',
  4: 'Blu-ray',
  99: 'その他',
};

/** 提供形態を表示名にして並べる */
export function releaseFormatText(formats: ReleaseFormatValue[]): string {
  return formats.map((format) => RELEASE_FORMAT_NAMES[format]).join('・');
}

/** 楽曲詳細へのリンク先。非表示の楽曲はリンクしない */
function songHref(song: ReleaseTrackSong): string | null {
  return song.isDisplay ? `/songs/${song.songId}` : null;
}

/**
 * 曲の一覧に出す 1 項目。イベントのセットリストと同じ見た目にする
 * 1 曲のトラックはトラック名を楽曲名の上書きとして出し、メドレーはトラック名を見出しにして楽曲を並べる
 */
export function trackSongListItem(track: ReleaseTrack) {
  const index = String(track.trackNo).padStart(2, '0');
  const [first, ...rest] = track.songs;

  if (first === undefined) {
    return { index, label: null, songs: [{ title: track.title ?? '', href: null, note: null }] };
  }

  if (rest.length === 0) {
    return { index, label: null, songs: [{ title: track.title ?? first.title, href: songHref(first), note: null }] };
  }

  return {
    index,
    label: track.title,
    songs: track.songs.map((song) => ({ title: song.title, href: songHref(song), note: null })),
  };
}

/** 検索に使うトラックの名前。トラック名と楽曲名の両方で引けるようにする */
export function trackSearchTitles(track: ReleaseTrack): string[] {
  return [...(track.title === null ? [] : [track.title]), ...track.songs.map((song) => song.title)];
}
