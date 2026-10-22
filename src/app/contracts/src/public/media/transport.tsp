import "../pagination.tsp";
import "./domain.tsp";

namespace IsekaiObservatory.Public;

model MediaListResponse {
  items: Media[];

  @doc("続きがある場合のみ返す")
  nextPageToken?: pageToken;
}
