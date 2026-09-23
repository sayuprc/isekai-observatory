import "@typespec/http";
import "../../shared";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Viewer;

@tag("Event")
@route("/events")
interface EventService {
  @doc("公開イベント一覧取得API")
  listEvents(@query cursor?: cursor, @query limit?: limit): Ok<EventListResponse> | InternalServerError;
}
