import type {
  ReleaseFormatValue,
  ReleaseGroupListItem,
  ReleaseGroupTypeValue,
  ReleaseListItem,
  ReleaseMediumItem,
  ReleaseTrackItem,
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

/** トラック詳細へのリンク可否。楽曲未紐づけまたは非表示のトラックはリンクしない */
export function isLinkableTrack(track: ReleaseTrack): track is ReleaseTrack & { songId: string } {
  return track.songId !== null && track.isDisplay;
}

/** 曲の一覧に出す 1 項目。イベントのセットリストと同じ見た目にするため、1 トラックを 1 曲の項目にする */
export function trackSongListItem(track: ReleaseTrack) {
  return {
    index: String(track.trackNo).padStart(2, '0'),
    label: null,
    songs: [{ title: track.title, href: isLinkableTrack(track) ? `/songs/${track.songId}` : null, note: null }],
  };
}
