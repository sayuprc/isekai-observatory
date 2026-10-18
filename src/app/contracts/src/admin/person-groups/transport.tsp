import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

model RequestPersonGroupMember is PickProperties<PersonGroupMember, "personId" | "orderNo">;

model PersonGroupSearchResponse {
  personGroups: PersonGroupSummary[];
  maxPage: int32;
}

model PersonGroupGetResponse {
  personGroup: PersonGroup;
}

model PersonGroupCreateRequest {
  name: personGroupName;
  displayName: personGroupDisplayName;
  members: RequestPersonGroupMember[];
}

model PersonGroupCreateResponse {
  personGroup: PersonGroup;
}

model PersonGroupUpdateRequest {
  name: personGroupName;
  displayName: personGroupDisplayName;
  members: RequestPersonGroupMember[];
}

model PersonGroupUpdateResponse {
  personGroup: PersonGroup;
}
