import "../pagination.tsp";
import "./domain.tsp";

namespace IsekaiObservatory.Public;

model PersonListResponse {
  items: Person[];

  @doc("続きがある場合のみ返す")
  nextPageToken?: pageToken;
}
