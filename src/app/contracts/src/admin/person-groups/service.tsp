import "@typespec/http";
import "../auth";
import "../../shared";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Admin;

@tag("PersonGroup")
@route("/person-groups")
@useAuth(BearerAuth)
interface PersonGroupService {
  @doc("人物グループ検索API。名前の昇順で返す")
  @route("/search")
  searchPersonGroups(
    @query name?: string,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.fifty,
  ): Ok<PersonGroupSearchResponse> | Unauthorized | Forbidden | ServerError;

  @doc("人物グループ取得API")
  getPersonGroup(@path personGroupId: uuid):
    | Ok<PersonGroupGetResponse>
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("人物グループ作成API")
  createPersonGroup(@body request: PersonGroupCreateRequest):
    | Ok<PersonGroupCreateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;

  @doc("人物グループ更新API")
  @put
  updatePersonGroup(@path personGroupId: uuid, @body request: PersonGroupUpdateRequest):
    | Ok<PersonGroupUpdateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("人物グループ削除API。楽曲披露から参照中のグループは削除できない")
  @delete
  deletePersonGroup(@path personGroupId: uuid):
    | NoContent
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;
}
