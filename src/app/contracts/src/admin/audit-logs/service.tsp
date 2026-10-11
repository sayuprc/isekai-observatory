import "@typespec/http";
import "../auth";
import "../../shared";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Admin;

@tag("AuditLog")
@route("/audit-logs")
@useAuth(BearerAuth)
interface AuditLogService {
  @doc("監査ログ検索API")
  @route("/search")
  searchAuditLogs(
    @query from?: utcDateTime,
    @query to?: utcDateTime,
    @query action?: AuditAction,
    @query("target_type") targetType?: AuditTargetType,
    @query("target_id") targetId?: uuid,
    @query("admin_user_name") adminUserName?: string,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.fifty,
  ): Ok<AuditLogSearchResponse> | Unauthorized | Forbidden | ServerError;

  @doc("監査ログ取得API")
  getAuditLog(@path auditLogId: uuid): Ok<AuditLogGetResponse> | Unauthorized | Forbidden | NotFound | ServerError;
}
