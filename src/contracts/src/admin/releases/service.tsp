import "@typespec/http";
import "../auth";
import "../../shared";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Admin;

@doc("リリースグループ検索のソート条件")
enum ReleaseGroupSearchSortBy {
  firstReleasedOn: "first_released_on",
  title: "title",
}

@tag("ReleaseGroup")
@route("/release-groups")
@useAuth(BearerAuth)
interface ReleaseGroupService {
  @doc("リリースグループ作成API")
  createReleaseGroup(@body request: ReleaseGroupCreateRequest):
    | Ok<ReleaseGroupCreateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;

  @doc("リリースグループ取得API")
  getReleaseGroup(@path releaseGroupId: uuid):
    | Ok<ReleaseGroupGetResponse>
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("リリースグループ更新API")
  @put
  updateReleaseGroup(@path releaseGroupId: uuid, @body request: ReleaseGroupUpdateRequest):
    | Ok<ReleaseGroupUpdateResponse>
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("リリースグループ削除API")
  @delete
  deleteReleaseGroup(@path releaseGroupId: uuid):
    | NoContent
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("リリースグループ検索API")
  @route("/search")
  searchReleaseGroups(
    @query title?: string,
    @query type?: ReleaseGroupTypeValue,
    @query("is_display") isDisplay?: boolean,
    @query sort?: ReleaseGroupSearchSortBy = ReleaseGroupSearchSortBy.firstReleasedOn,
    @query order?: SortOrder = SortOrder.desc,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.twentyFive,
  ): Ok<ReleaseGroupSearchResponse> | Unauthorized | Forbidden | ServerError;
}

@tag("Release")
@route("/releases")
@useAuth(BearerAuth)
interface ReleaseService {
  @doc("リリース作成API")
  createRelease(@body request: ReleaseCreateRequest):
    | Ok<ReleaseCreateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;

  @doc("リリース取得API")
  getRelease(@path releaseId: uuid):
    | Ok<ReleaseGetResponse>
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("リリース更新API")
  @put
  updateRelease(@path releaseId: uuid, @body request: ReleaseUpdateRequest):
    | Ok<ReleaseUpdateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("リリース削除API")
  @delete
  deleteRelease(@path releaseId: uuid): NoContent | Unauthorized | Forbidden | NotFound | Unprocessable | ServerError;
}
