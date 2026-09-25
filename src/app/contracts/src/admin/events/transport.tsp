import "../../shared";
import "../../packages/event";
import "../../packages/media";
import "../../packages/song";
import "../venues";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Event;
using IsekaiObservatory.Packages.Media;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Admin;

model EventSearchResponse {
  events: EventSummary[];
  maxPage: int32;
}

model EventGetResponse {
  event: Event;
}

model EventCreateRequest {
  title: eventTitle;
  description: eventDescription;
  typeValue: EventTypeValue;
  schedule: EventSchedule;
  statusValue: EventStatusValue;
  isDisplay: boolean;
  venues: RequestEventVenueLink[];
  media: RequestEventMediaLink[];
  sources: EventSource[];
  performances: RequestSongPerformance[];
  setlist: RequestSetlistItem[];
}

model EventCreateResponse {
  event: Event;
}

model EventUpdateRequest {
  title: eventTitle;
  description: eventDescription;
  typeValue: EventTypeValue;
  schedule: EventSchedule;
  statusValue: EventStatusValue;
  isDisplay: boolean;
  venues: RequestEventVenueLink[];
  media: RequestEventMediaLink[];
  sources: EventSource[];
  performances: RequestSongPerformance[];
  setlist: RequestSetlistItem[];
}

model EventUpdateResponse {
  event: Event;
}

model RequestEventVenueLink {
  venueId: venueId;
  orderNo: orderNo;
}

model RequestEventMediaLink {
  mediaId: mediaId;
  orderNo: orderNo;
}

model RequestPerformancePerson is PickProperties<PerformancePerson, "personId" | "creditName" | "orderNo">;

model RequestSongPerformance {
  ...PickProperties<SongPerformance, "performanceId" | "songId" | "orderNo">;
  coVocalists: RequestPerformancePerson[];
}

model RequestSetlistItem {
  ...PickProperties<SetlistItem, "setlistItemId" | "orderNo" | "label">;
  performanceIds: performanceId[];
}
