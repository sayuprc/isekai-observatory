import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

model VenueSearchResponse {
  venues: VenueSummary[];
  maxPage: int32;
}

model VenueGetResponse {
  venue: Venue;
}

model VenueCreateRequest {
  name: venueName;
  kind: VenueKindValue;
}

model VenueCreateResponse {
  venue: Venue;
}

model VenueUpdateRequest {
  name: venueName;
  kind: VenueKindValue;
}

model VenueUpdateResponse {
  venue: Venue;
}
