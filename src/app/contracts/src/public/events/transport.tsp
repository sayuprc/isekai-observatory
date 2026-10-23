import "../pagination.tsp";
import "./domain.tsp";

namespace IsekaiObservatory.Public;

model EventListResponse {
  items: Event[];

  @doc("続きがある場合のみ返します")
  nextPageToken?: pageToken;
}
