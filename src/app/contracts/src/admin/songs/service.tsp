import "@typespec/http";
import "../../shared";
import "../auth";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Admin;

@doc("楽曲検索のソート条件")
enum SongSearchSortBy {
  title: "title",
  orderNo: "order_no",
}

@doc("楽曲検索API用の楽曲種別")
enum SongSearchTypeValue {
  Original: "1",
  Cover: "2",
}

@tag("Song")
@route("/songs")
@useAuth(BearerAuth)
interface SongService {
  @doc("楽曲検索API")
  @route("/search")
  searchSongs(
    @query title?: string,
    @query type?: SongSearchTypeValue,
    @query("is_display") isDisplay?: boolean,
    @query sort?: SongSearchSortBy = SongSearchSortBy.orderNo,
    @query order?: SortOrder = SortOrder.asc,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.fifty,
  ): Ok<SongSearchResponse> | Unauthorized | Forbidden | ServerError;

  @doc("楽曲取得API")
  getSong(@path songId: uuid): Ok<SongGetResponse> | Unauthorized | Forbidden | NotFound | Unprocessable | ServerError;

  @doc("楽曲作成API")
  createSong(@body request: SongCreateRequest):
    | Ok<SongCreateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;

  @doc("楽曲更新API")
  @put
  updateSong(@path songId: uuid, @body request: SongUpdateRequest):
    | Ok<SongUpdateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("楽曲削除API")
  @delete
  deleteSong(@path songId: uuid): NoContent | BadRequest | Unauthorized | Forbidden | Unprocessable | ServerError;
}
