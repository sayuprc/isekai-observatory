import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

@doc("監査ログID")
scalar auditLogId extends uuid;

@doc("監査ログの操作種別")
@IsekaiObservatory.Shared.enumExtensions
enum AuditAction {
  @doc("作成")
  create: "create",

  @doc("更新")
  update: "update",

  @doc("削除")
  delete: "delete",

  @doc("登録")
  register: "register",

  @doc("ログイン")
  login: "login",

  @doc("リフレッシュ")
  refresh: "refresh",

  @doc("リカバリーコード発行")
  recoveryCodeIssue: "recovery_code_issue",

  @doc("リカバリーコード使用")
  recoveryCodeUse: "recovery_code_use",
}

@doc("監査ログの対象種別")
@IsekaiObservatory.Shared.enumExtensions
enum AuditTargetType {
  @doc("管理ユーザー")
  adminUser: "AdminUser",

  @doc("メディア")
  media: "Media",

  @doc("人物")
  person: "Person",

  @doc("人物グループ")
  personGroup: "PersonGroup",

  @doc("リリース")
  release: "Release",

  @doc("リリースグループ")
  releaseGroup: "ReleaseGroup",

  @doc("楽曲")
  song: "Song",

  @doc("楽曲タグ")
  songTag: "SongTag",

  @doc("開催先")
  venue: "Venue",

  @doc("イベント")
  event: "Event",
}

@doc("監査ログ一覧の 1 行分")
model AuditLogSummary {
  auditLogId: auditLogId;
  adminUserId: uuid;
  adminUserName: string;
  action: AuditAction;
  targetType: AuditTargetType;
  targetId: uuid;
  createdAt: utcDateTime;
}

@doc("監査ログ詳細")
model AuditLog {
  ...AuditLogSummary;

  @doc("対象の変更後スナップショット。target_type ごとに構造が異なるため任意の JSON とする")
  snapshot: Record<unknown>;
}
