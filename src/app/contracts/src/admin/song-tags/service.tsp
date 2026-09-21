import "@typespec/http";
import "../../shared";
import "../auth";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Admin;

@doc("楽曲タグ検索のソート条件")
enum SongTagSearchSortBy {
  name: "name",
  orderNo: "order_no",
}

@tag("SongTag")
@route("/song-tags")
@useAuth(BearerAuth)
interface SongTagService {
  @doc("楽曲タグ一覧取得API")
  listSongTags(): Ok<SongTagListResponse> | Unauthorized | Forbidden | ServerError;

  @doc("楽曲タグ検索API")
  @route("/search")
  searchSongTags(
    @query name?: string,
    @query sort?: SongTagSearchSortBy = SongTagSearchSortBy.orderNo,
    @query order?: SortOrder = SortOrder.asc,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.fifty,
  ): Ok<SongTagSearchResponse> | Unauthorized | Forbidden | ServerError;

  @doc("楽曲タグ取得API")
  getSongTag(@path songTagId: uuid):
    | Ok<SongTagGetResponse>
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("楽曲タグ作成API")
  createSongTag(@body request: SongTagCreateRequest):
    | Ok<SongTagCreateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;

  @doc("楽曲タグ更新API")
  @put
  updateSongTag(@path songTagId: uuid, @body request: SongTagUpdateRequest):
    | Ok<SongTagUpdateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("楽曲タグ削除API")
  @delete
  deleteSongTag(@path songTagId: uuid): NoContent | BadRequest | Unauthorized | Forbidden | Unprocessable | ServerError;
}
