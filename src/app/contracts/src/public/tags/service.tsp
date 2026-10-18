import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("Tag")
@route("/tags")
interface TagService {
  @doc("タグ一覧取得API")
  listTags(@query pageToken?: pageToken, @query pageSize?: pageSize):
    | Ok<TagListResponse>
    | BadRequest
    | InternalServerError;
}
