import "../../shared";
import "../../packages/event";
import "../../packages/media";
import "../../packages/song";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Event;
using IsekaiObservatory.Packages.Media;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Viewer;

model EventVenueSummary {
  venueId: uuid;
  name: string;
  kindName: string;
}

model EventMediaSummary {
  mediaId: mediaId;
  title: mediaTitle;
  url: mediaUrl;
  publishedAt: mediaPublishedAt;
  type: MediaType;
}

model EventSource {
  displayName: eventSourceName;
  url: eventSourceUrl;
  orderNo: orderNo;
}

model PerformancePerson {
  personId: uuid;
  name: string;
  creditName: performanceCreditName | null;
  orderNo: orderNo;
}

model SongPerformance {
  performanceId: uuid;
  songId: songId | null;
  songTitle: title;
  coVocalists: PerformancePerson[];
}

model SetlistItem {
  orderNo: orderNo;
  label: string | null;
  performances: SongPerformance[];
}

model Event {
  eventId: eventId;
  title: eventTitle;
  description: eventDescription | null;
  typeValue: EventTypeValue;
  schedule: EventSchedule;
  statusValue: EventStatusValue | null;
  postponedToEventId: eventId | null;
  venues: EventVenueSummary[];
  media: EventMediaSummary[];
  sources: EventSource[];
  performances: SongPerformance[];
  setlist: SetlistItem[];
}
