import "../pagination.tsp";
import "./domain.tsp";

namespace IsekaiObservatory.Public;

model ReleaseGroupListResponse {
  items: ReleaseGroup[];

  @doc("続きがある場合のみ返す")
  nextPageToken?: pageToken;
}
