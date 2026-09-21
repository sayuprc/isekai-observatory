import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

@doc("監査ログID")
scalar auditLogId extends uuid;

@doc("監査ログの操作種別")
enum AuditAction {
  create: "create",
  update: "update",
  delete: "delete",
  register: "register",
  login: "login",
  refresh: "refresh",
  recoveryCodeIssue: "recovery_code_issue",
  recoveryCodeUse: "recovery_code_use",
}

@doc("監査ログの対象種別")
enum AuditTargetType {
  adminUser: "AdminUser",
  media: "Media",
  person: "Person",
  release: "Release",
  releaseGroup: "ReleaseGroup",
  song: "Song",
  songTag: "SongTag",
  venue: "Venue",
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
