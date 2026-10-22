import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("ReleaseGroup")
@route("/release-groups")
interface ReleaseGroupService {
  @doc("リリースグループ一覧取得API")
  listReleaseGroups(@query pageToken?: pageToken, @query pageSize?: pageSize):
    | Ok<ReleaseGroupListResponse>
    | BadRequest
    | InternalServerError;
}
