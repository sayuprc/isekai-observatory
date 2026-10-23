import "../pagination.tsp";
import "./domain.tsp";

namespace IsekaiObservatory.Public;

model TagListResponse {
  items: Tag[];

  @doc("続きがある場合のみ返します")
  nextPageToken?: pageToken;
}
