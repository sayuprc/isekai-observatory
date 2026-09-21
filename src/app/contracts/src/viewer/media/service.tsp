import "@typespec/http";
import "../../shared";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Viewer;

@tag("Media")
@route("/media")
interface MediaService {
  @doc("メディア一覧取得API")
  listMedia(@query cursor?: cursor, @query limit?: limit): Ok<MediaListResponse> | InternalServerError;
}
