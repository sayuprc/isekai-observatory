import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("Venue")
@route("/venues")
interface VenueService {
  @doc("開催先一覧取得API")
  listVenues(@query pageToken?: pageToken, @query pageSize?: pageSize):
    | Ok<VenueListResponse>
    | BadRequest
    | InternalServerError;
}
