import "@typespec/http";
import "../../shared";
import "../auth";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Admin;

@tag("SongType")
@route("/song-types")
@useAuth(BearerAuth)
interface SongTypeService {
  @doc("楽曲種別一覧取得API")
  listSongTypes(): Ok<SongTypeListResponse> | Unauthorized | Forbidden | ServerError;
}
