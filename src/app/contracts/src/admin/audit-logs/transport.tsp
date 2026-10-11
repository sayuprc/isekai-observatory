namespace IsekaiObservatory.Admin;

model AuditLogSearchResponse {
  auditLogs: AuditLogSummary[];
  maxPage: int32;
}

model AuditLogGetResponse {
  auditLog: AuditLog;
}
