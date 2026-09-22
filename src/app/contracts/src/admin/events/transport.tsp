import "../../shared";
import "../../packages/event";
import "../../packages/media";
import "../../packages/song";

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
  description: eventDescription | null;
  typeValue: EventTypeValue;
  schedule: EventSchedule;
  statusValue: EventStatusValue | null;
  postponedToEventId: eventId | null;
  isDisplay: boolean;
  venueIds: uuid[];
  mediaIds: mediaId[];
  sources: EventSource[];
  performances: SongPerformance[];
  setlist: SetlistItem[];
}

model EventCreateResponse {
  event: Event;
}

model EventUpdateRequest {
  title: eventTitle;
  description: eventDescription | null;
  typeValue: EventTypeValue;
  schedule: EventSchedule;
  statusValue: EventStatusValue | null;
  postponedToEventId: eventId | null;
  isDisplay: boolean;
  venueIds: uuid[];
  mediaIds: mediaId[];
  sources: EventSource[];
  performances: SongPerformance[];
  setlist: SetlistItem[];
}

model EventUpdateResponse {
  event: Event;
}
