import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

model TagListResponse {
  tags: Tag[];
}

model TagSearchResponse {
  tags: TagSummary[];
  maxPage: int32;
}

model TagGetResponse {
  tag: Tag;
}

model TagCreateRequest {
  name: tagName;
}

model TagCreateResponse {
  tag: Tag;
}

model TagUpdateRequest {
  name: tagName;
  orderNo: orderNo;
}

model TagUpdateResponse {
  tag: Tag;
}
