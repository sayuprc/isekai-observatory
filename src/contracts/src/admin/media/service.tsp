import "@typespec/http";
import "../auth";
import "../../packages/media";
import "../../shared";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Media;
using Http;

namespace IsekaiObservatory.Admin;

@doc("メディア検索のソート条件")
enum MediaSearchSortBy {
  publishedAt: "published_at",
  title: "title",
}

@tag("Media")
@route("/media")
@useAuth(BearerAuth)
interface MediaService {
  @doc("メディア取得API")
  getMedia(@path mediaId: uuid):
    | Ok<MediaGetResponse>
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("メディア検索API")
  @route("/search")
  searchMedia(
    @query title?: string,
    @query type?: MediaTypeValue,
    @query("is_display") isDisplay?: boolean,
    @query sort?: MediaSearchSortBy = MediaSearchSortBy.publishedAt,
    @query order?: SortOrder = SortOrder.asc,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.twentyFive,
  ): Ok<MediaSearchResponse> | Unauthorized | Forbidden | ServerError;

  @doc("メディア作成API")
  createMedia(@body request: MediaCreateRequest):
    | Ok<MediaCreateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;

  @doc("メディア更新API")
  @put
  updateMedia(@path mediaId: uuid, @body request: MediaUpdateRequest):
    | Ok<MediaUpdateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("メディア削除API")
  @delete
  deleteMedia(@path mediaId: uuid): NoContent | BadRequest | Unauthorized | Forbidden | Unprocessable | ServerError;
}
