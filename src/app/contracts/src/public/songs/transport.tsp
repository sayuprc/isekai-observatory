import "../pagination.tsp";
import "./domain.tsp";

namespace IsekaiObservatory.Public;

model SongListResponse {
  items: Song[];

  @doc("続きがある場合のみ返す")
  nextPageToken?: pageToken;
}
