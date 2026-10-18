import "@typespec/http";
import "../../shared";
import "../auth";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Admin;

@doc("タグ検索のソート条件")
@enumExtensions
enum TagSearchSortBy {
  @doc("タグ名")
  name: "name",

  @doc("表示順")
  orderNo: "order_no",
}

@tag("Tag")
@route("/tags")
@useAuth(BearerAuth)
interface TagService {
  @doc("タグ一覧取得API")
  listTags(): Ok<TagListResponse> | Unauthorized | Forbidden | ServerError;

  @doc("タグ検索API")
  @route("/search")
  searchTags(
    @query name?: string,
    @query sort?: TagSearchSortBy = TagSearchSortBy.orderNo,
    @query order?: SortOrder = SortOrder.asc,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.fifty,
  ): Ok<TagSearchResponse> | Unauthorized | Forbidden | ServerError;

  @doc("タグ取得API")
  getTag(@path tagId: tagId): Ok<TagGetResponse> | Unauthorized | Forbidden | NotFound | Unprocessable | ServerError;

  @doc("タグ作成API")
  createTag(@body request: TagCreateRequest):
    | Ok<TagCreateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;

  @doc("タグ更新API")
  @put
  updateTag(@path tagId: tagId, @body request: TagUpdateRequest):
    | Ok<TagUpdateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("タグ削除API")
  @delete
  deleteTag(@path tagId: tagId): NoContent | BadRequest | Unauthorized | Forbidden | Unprocessable | ServerError;
}
