import "../../shared";
import "../../packages/event";
import "../../packages/media";
import "../../packages/song";
import "../media";
import "../persons";
import "../venues";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Event;
using IsekaiObservatory.Packages.Media;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Admin;

@doc("活動出典")
model EventSource {
  displayName: eventSourceName;
  url: eventSourceUrl;
  orderNo: orderNo;
}

@doc("活動に紐づく楽曲披露")
model PerformancePerson {
  personId: personId;
  name: personName;
  creditName: performanceCreditName | null;
  orderNo: orderNo;
}

@doc("活動に紐づく楽曲披露")
model SongPerformance {
  performanceId: uuid;
  songId: songId;
  songTitle: title;
  orderNo: orderNo;
  coVocalists: PerformancePerson[];
  isDisplay: boolean;
}

@doc("セットリストの 1 項目")
model SetlistItem {
  setlistItemId: uuid;
  orderNo: orderNo;
  label: string | null;
  performances: SongPerformance[];
}

@doc("活動")
model Event {
  eventId: eventId;
  title: eventTitle;
  description: eventDescription;
  typeValue: EventTypeValue;
  schedule: EventSchedule;
  statusValue: EventStatusValue;
  isDisplay: boolean;
  venues: Venue[];
  media: Media[];
  sources: EventSource[];
  performances: SongPerformance[];
  setlist: SetlistItem[];
}

@doc("活動一覧の 1 行分")
model EventSummary {
  eventId: eventId;
  title: eventTitle;
  typeValue: EventTypeValue;
  schedule: EventSchedule;
  statusValue: EventStatusValue;
  isDisplay: boolean;
}
