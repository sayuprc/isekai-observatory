import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("Venue")
@route("/venues")
interface VenueService {
  @summary("開催先一覧")
  @doc("公開イベントから参照されている開催先を返す。並び順は名前順")
  @returnsDoc("開催先の一覧")
  listVenues(@query pageToken?: pageToken, @query pageSize?: pageSize):
    | Ok<VenueListResponse>
    | BadRequest
    | InternalServerError;
}
