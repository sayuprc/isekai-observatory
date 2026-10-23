import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("Media")
@route("/media")
interface MediaService {
  @summary("メディア一覧")
  @doc("公開メディアを返します。並び順はタイトル順です")
  @returnsDoc("メディアの一覧")
  listMedia(@query pageToken?: pageToken, @query pageSize?: pageSize):
    | Ok<MediaListResponse>
    | BadRequest
    | InternalServerError;
}
