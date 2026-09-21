import "@typespec/http";
import "../../shared";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Viewer;

@tag("Song")
@route("/songs")
interface SongService {
  @doc("楽曲一覧取得API")
  listSongs(@query cursor?: cursor, @query limit?: limit): Ok<SongListResponse> | InternalServerError;
}
