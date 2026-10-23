import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("Song")
@route("/songs")
interface SongService {
  @summary("楽曲一覧")
  @doc("公開楽曲を返す。並び順は表示順の降順")
  @returnsDoc("楽曲の一覧")
  listSongs(@query pageToken?: pageToken, @query pageSize?: pageSize):
    | Ok<SongListResponse>
    | BadRequest
    | InternalServerError;
}
