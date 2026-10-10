import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("PersonGroup")
@route("/person-groups")
interface PersonGroupService {
  @doc("人物グループ一覧取得API")
  listPersonGroups(@query pageToken?: pageToken, @query pageSize?: pageSize):
    | Ok<PersonGroupListResponse>
    | BadRequest
    | InternalServerError;
}
