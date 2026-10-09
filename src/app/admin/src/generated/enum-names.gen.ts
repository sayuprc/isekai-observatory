// このファイルは scripts/generate-enum-names.ts が契約から生成する。直接編集しない
import type { AuditAction, AuditTargetType, ErrorCode, EventSearchSortBy, EventStatusValue, EventTypeValue, MediaSearchSortBy, MediaTypeValue, PermissionValue, PerPage, PersonSearchSortBy, ReleaseFormatValue, ReleaseGroupSearchSortBy, ReleaseGroupTypeValue, RoleValue, SongPersonRole, SongSearchSortBy, SongSearchTypeValue, SongTagSearchSortBy, SongTypeValue, SortOrder, VenueKindValue, VenueSearchSortBy, Version } from './types.gen';

export const AUDIT_ACTION_NAMES: Record<AuditAction, string> = {
  "create": "作成",
  "update": "更新",
  "delete": "削除",
  "register": "登録",
  "login": "ログイン",
  "refresh": "リフレッシュ",
  "recovery_code_issue": "リカバリーコード発行",
  "recovery_code_use": "リカバリーコード使用",
};

export const AUDIT_TARGET_TYPE_NAMES: Record<AuditTargetType, string> = {
  "AdminUser": "管理ユーザー",
  "Media": "メディア",
  "Person": "人物",
  "PersonGroup": "人物グループ",
  "Release": "リリース",
  "ReleaseGroup": "リリースグループ",
  "Song": "楽曲",
  "SongTag": "楽曲タグ",
  "Venue": "開催先",
  "Event": "イベント",
};

export const ERROR_CODE_NAMES: Record<ErrorCode, string> = {
  "unauthenticated": "認証されていない (401)",
  "permission_denied": "権限がない (403)",
  "not_found": "対象が見つからない (404)",
  "validation_failed": "入力形式の検証に失敗した (422)",
  "business_rule_violation": "業務ルールに違反した (400)",
  "internal_error": "予期しないエラーが発生した (500)",
};

export const EVENT_SEARCH_SORT_BY_NAMES: Record<EventSearchSortBy, string> = {
  "schedule": "開催時期",
  "title": "タイトル",
};

export const EVENT_STATUS_NAMES: Record<EventStatusValue, string> = {
  "1": "通常",
  "2": "延期",
  "3": "中止",
};

export const EVENT_TYPE_NAMES: Record<EventTypeValue, string> = {
  "1": "ライブ",
  "2": "配信",
  "3": "展覧会",
  "4": "ラジオ",
  "99": "その他",
};

export const MEDIA_SEARCH_SORT_BY_NAMES: Record<MediaSearchSortBy, string> = {
  "published_at": "公開日",
  "title": "タイトル",
};

export const MEDIA_TYPE_NAMES: Record<MediaTypeValue, string> = {
  "1": "MV",
  "2": "音源動画",
  "3": "配信",
  "4": "ショート",
  "5": "投稿",
  "99": "その他",
};

export const PERMISSION_NAMES: Record<PermissionValue, string> = {
  "read_admin_user": "管理ユーザー閲覧",
  "write_admin_user": "管理ユーザー編集",
  "read_person": "人物閲覧",
  "write_person": "人物編集",
  "read_song": "楽曲閲覧",
  "write_song": "楽曲編集",
  "read_audit_log": "監査ログ閲覧",
  "read_media": "メディア閲覧",
  "write_media": "メディア編集",
  "read_release": "リリース閲覧",
  "write_release": "リリース編集",
  "read_venue": "開催先閲覧",
  "write_venue": "開催先編集",
  "read_event": "イベント閲覧",
  "write_event": "イベント編集",
};

export const PER_PAGE_NAMES: Record<PerPage, string> = {
  "25": "25 件",
  "50": "50 件",
  "100": "100 件",
};

export const PERSON_SEARCH_SORT_BY_NAMES: Record<PersonSearchSortBy, string> = {
  "name": "人物名",
  "order_no": "表示順",
};

export const RELEASE_FORMAT_NAMES: Record<ReleaseFormatValue, string> = {
  "1": "配信",
  "2": "CD",
  "3": "DVD",
  "4": "Blu-ray",
  "99": "その他",
};

export const RELEASE_GROUP_SEARCH_SORT_BY_NAMES: Record<ReleaseGroupSearchSortBy, string> = {
  "first_released_on": "初リリース日",
  "title": "タイトル",
};

export const RELEASE_GROUP_TYPE_NAMES: Record<ReleaseGroupTypeValue, string> = {
  "1": "シングル",
  "2": "アルバム",
  "3": "EP",
  "99": "その他",
};

export const ROLE_NAMES: Record<RoleValue, string> = {
  "1": "特権",
  "2": "コンソール",
  "3": "一般",
};

export const SONG_PERSON_ROLE_NAMES: Record<SongPersonRole, string> = {
  "1": "作詞",
  "2": "作曲",
  "3": "編曲",
};

export const SONG_SEARCH_SORT_BY_NAMES: Record<SongSearchSortBy, string> = {
  "title": "楽曲名",
  "order_no": "表示順",
};

export const SONG_SEARCH_TYPE_NAMES: Record<SongSearchTypeValue, string> = {
  "1": "オリジナル曲",
  "2": "カバー曲",
};

export const SONG_TAG_SEARCH_SORT_BY_NAMES: Record<SongTagSearchSortBy, string> = {
  "name": "楽曲タグ名",
  "order_no": "表示順",
};

export const SONG_TYPE_NAMES: Record<SongTypeValue, string> = {
  "1": "オリジナル曲",
  "2": "カバー曲",
};

export const SORT_ORDER_NAMES: Record<SortOrder, string> = {
  "asc": "昇順",
  "desc": "降順",
};

export const VENUE_KIND_NAMES: Record<VenueKindValue, string> = {
  "1": "現地",
  "2": "オンライン",
};

export const VENUE_SEARCH_SORT_BY_NAMES: Record<VenueSearchSortBy, string> = {
  "name": "開催先名",
};

export const VERSION_NAMES: Record<Version, string> = {
  "v1": "バージョン 1",
};
