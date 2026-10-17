import "@typespec/http";
import "../../shared";
import "../../packages/song";
import "../auth";
import "../tags";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Song;
using Http;

namespace IsekaiObservatory.Admin;

@doc("楽曲検索のソート条件")
@enumExtensions
enum SongSearchSortBy {
  @doc("楽曲名")
  title: "title",

  @doc("表示順")
  orderNo: "order_no",
}

@doc("楽曲検索API用の楽曲種別")
@enumExtensions
enum SongSearchTypeValue {
  @doc("オリジナル曲")
  Original: "1",

  @doc("カバー曲")
  Cover: "2",

  @doc("参加曲")
  Guest: "3",
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

    @doc("このタグが付いた楽曲だけに絞り込む")
    @query("tag_id")
    tagId?: tagId,

    @query sort?: SongSearchSortBy = SongSearchSortBy.orderNo,
    @query order?: SortOrder = SortOrder.asc,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.fifty,
  ): Ok<SongSearchResponse> | Unauthorized | Forbidden | ServerError;

  @doc("楽曲取得API")
  getSong(@path songId: songId):
    | Ok<SongGetResponse>
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

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
  updateSong(@path songId: songId, @body request: SongUpdateRequest):
    | Ok<SongUpdateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("楽曲削除API")
  @delete
  deleteSong(@path songId: songId): NoContent | BadRequest | Unauthorized | Forbidden | Unprocessable | ServerError;
}
