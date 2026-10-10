import "../pagination.tsp";
import "./domain.tsp";

namespace IsekaiObservatory.Public;

model PersonGroupListResponse {
  items: PersonGroup[];

  @doc("続きがある場合のみ返す")
  nextPageToken?: pageToken;
}
