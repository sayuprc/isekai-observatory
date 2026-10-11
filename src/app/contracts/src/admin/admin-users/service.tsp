import "@typespec/http";
import "../../shared";
import "../auth";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Admin;

@tag("AdminUser")
@route("/admin-users")
@useAuth(BearerAuth)
interface AdminUserService {
  @doc("管理ユーザー一覧取得API")
  listAdminUsers(): Ok<AdminUserListResponse> | Unauthorized | Forbidden | ServerError;
}
