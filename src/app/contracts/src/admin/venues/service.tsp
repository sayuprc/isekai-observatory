import "@typespec/http";
import "../auth";
import "../../shared";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Admin;

@doc("開催先検索のソート条件")
enum VenueSearchSortBy {
  name: "name",
}

@tag("Venue")
@route("/venues")
@useAuth(BearerAuth)
interface VenueService {
  @doc("開催先検索API")
  @route("/search")
  searchVenues(
    @query name?: string,
    @query kind?: VenueKindValue,
    @query sort?: VenueSearchSortBy = VenueSearchSortBy.name,
    @query order?: SortOrder = SortOrder.asc,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.twentyFive,
  ): Ok<VenueSearchResponse> | Unauthorized | Forbidden | ServerError;

  @doc("開催先取得API")
  getVenue(@path venueId: uuid):
    | Ok<VenueGetResponse>
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("開催先作成API")
  createVenue(@body request: VenueCreateRequest):
    | Ok<VenueCreateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;

  @doc("開催先更新API")
  @put
  updateVenue(@path venueId: uuid, @body request: VenueUpdateRequest):
    | Ok<VenueUpdateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("開催先削除API")
  @delete
  deleteVenue(@path venueId: uuid):
    | NoContent
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;
}
