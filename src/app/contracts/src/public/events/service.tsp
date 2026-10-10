import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("Event")
@route("/events")
interface EventService {
  @doc("イベント一覧取得API")
  listEvents(@query pageToken?: pageToken, @query pageSize?: pageSize):
    | Ok<EventListResponse>
    | BadRequest
    | InternalServerError;
}
