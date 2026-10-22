import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("Song")
@route("/songs")
interface SongService {
  @doc("楽曲一覧取得API")
  listSongs(@query pageToken?: pageToken, @query pageSize?: pageSize):
    | Ok<SongListResponse>
    | BadRequest
    | InternalServerError;
}
