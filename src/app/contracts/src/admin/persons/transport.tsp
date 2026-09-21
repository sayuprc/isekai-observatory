import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

model PersonListResponse {
  persons: Person[];
}

model PersonSearchResponse {
  persons: Person[];
  maxPage: int32;
}

model PersonGetResponse {
  person: Person;
}

model PersonCreateRequest {
  name: personName;
}

model PersonCreateResponse {
  person: Person;
}

model PersonUpdateRequest {
  name: personName;
  orderNo: orderNo;
}

model PersonUpdateResponse {
  person: Person;
}
