import type {
  EventStatusValue,
  EventTypeValue,
  MediaTypeValue,
  ReleaseFormatValue,
  ReleaseGroupTypeValue,
  RoleValue,
  SongTypeValue,
  VenueKindValue,
} from '../generated';

// API は種別の値だけを返すので、表示名と選択肢はこの対応表から作る (ADR-0033)

export const SONG_TYPE_NAMES: Record<SongTypeValue, string> = {
  1: 'オリジナル曲',
  2: 'カバー曲',
};

export const MEDIA_TYPE_NAMES: Record<MediaTypeValue, string> = {
  1: 'MV',
  2: '音源動画',
  3: '配信',
  4: 'ショート',
  5: '投稿',
  99: 'その他',
};

export const EVENT_TYPE_NAMES: Record<EventTypeValue, string> = {
  1: 'ライブ',
  2: '配信',
  3: '展覧会',
  4: 'ラジオ',
  99: 'その他',
};

export const EVENT_STATUS_NAMES: Record<EventStatusValue, string> = {
  1: '通常',
  2: '延期',
  3: '中止',
};

export const VENUE_KIND_NAMES: Record<VenueKindValue, string> = {
  1: '現地',
  2: 'オンライン',
};

export const RELEASE_GROUP_TYPE_NAMES: Record<ReleaseGroupTypeValue, string> = {
  1: 'シングル',
  2: 'アルバム',
  3: 'EP',
  99: 'その他',
};

export const RELEASE_FORMAT_NAMES: Record<ReleaseFormatValue, string> = {
  1: '配信',
  2: 'CD',
  3: 'DVD',
  4: 'Blu-ray',
  99: 'その他',
};

export const ROLE_NAMES: Record<RoleValue, string> = {
  1: '特権',
  2: 'コンソール',
  3: '一般',
};

export type EnumOption<V> = { value: V; label: string };

// 数値のキーは昇順に列挙されるので、選択肢は値の順に並ぶ
export const toOptions = <V extends number>(names: Record<V, string>): EnumOption<V>[] =>
  Object.entries<string>(names).map(([value, label]) => ({ value: Number(value) as V, label }));
