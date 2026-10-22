import "../pagination.tsp";
import "./domain.tsp";

namespace IsekaiObservatory.Public;

model VenueListResponse {
  items: Venue[];

  @doc("続きがある場合のみ返す")
  nextPageToken?: pageToken;
}
