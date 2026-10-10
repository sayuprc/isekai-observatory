import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("Media")
@route("/media")
interface MediaService {
  @doc("メディア一覧取得API")
  listMedia(@query pageToken?: pageToken, @query pageSize?: pageSize):
    | Ok<MediaListResponse>
    | BadRequest
    | InternalServerError;
}
