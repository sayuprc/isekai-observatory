import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("ReleaseGroup")
@route("/release-groups")
interface ReleaseGroupService {
  @summary("リリースグループ一覧")
  @doc("公開リリースを 1 件以上持つリリースグループを返します。リリース (版) は `releases` に入れ子で返します。並び順は表示順の降順です")
  @returnsDoc("リリースグループの一覧")
  listReleaseGroups(@query pageToken?: pageToken, @query pageSize?: pageSize):
    | Ok<ReleaseGroupListResponse>
    | BadRequest
    | InternalServerError;
}
