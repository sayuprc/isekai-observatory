import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("Event")
@route("/events")
interface EventService {
  @summary("イベント一覧")
  @doc("公開イベントを返します。並び順はタイトル順です")
  @returnsDoc("イベントの一覧")
  listEvents(@query pageToken?: pageToken, @query pageSize?: pageSize):
    | Ok<EventListResponse>
    | BadRequest
    | InternalServerError;
}
