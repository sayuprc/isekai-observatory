import "../../shared";
import "../../packages/event";
import "../../packages/media";
import "../../packages/song";
import "../releases";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Event;
using IsekaiObservatory.Packages.Media;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Viewer;

@doc("開催先種別の値")
enum VenueKindValue {
  Physical: 1,
  Online: 2,
}

model EventVenueSummary {
  venueId: uuid;
  name: string;
  kind: VenueKindValue;
}

model EventMediaSummary {
  mediaId: mediaId;
  title: mediaTitle;
  url: mediaUrl;
  publishedAt: mediaPublishedAt;
  type: MediaTypeValue;
}

@doc("イベントに関連づけた公開リリース(版)。リリースとリリースグループがともに公開のものだけが載る")
model EventReleaseSummary {
  releaseId: releaseId;
  releaseGroupId: releaseGroupId;
  releaseGroupTitle: releaseGroupTitle;
  name: releaseName;
  releasedOn: releasedOn;
  formats: ReleaseFormatValue[];
}

model EventSource {
  displayName: eventSourceName;
  url: eventSourceUrl;
  orderNo: orderNo;
}

@doc("共演者が出演したグループ")
model PerformancePersonGroup {
  personGroupId: uuid;
  name: string;
}

@doc("楽曲披露の共演者。personGroup はグループとして全員で出演したときだけ持つ")
model PerformancePerson {
  personId: uuid;
  name: string;
  creditName: performanceCreditName | null;
  personGroup: PerformancePersonGroup | null;
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
  description: eventDescription;
  type: EventTypeValue;
  schedule: EventSchedule;
  status: EventStatusValue;
  venues: EventVenueSummary[];
  media: EventMediaSummary[];
  releases: EventReleaseSummary[];
  sources: EventSource[];
  performances: SongPerformance[];
  setlist: SetlistItem[];
}
