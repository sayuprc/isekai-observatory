import "@typespec/http";
import "../../shared";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Viewer;

@tag("ReleaseGroup")
@route("/release-groups")
interface ReleaseGroupService {
  @doc("リリースグループ一覧取得API")
  listReleaseGroups(@query cursor?: cursor, @query limit?: limit): Ok<ReleaseGroupListResponse> | InternalServerError;
}
