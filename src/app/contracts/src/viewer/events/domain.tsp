import "../../shared";
import "../tags";
import "../../packages/event";
import "../../packages/media";
import "../../packages/song";
import "../releases";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Event;
using IsekaiObservatory.Packages.Media;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Viewer;

@doc("開催先ID")
scalar venueId extends uuid;

@doc("人物ID")
scalar personId extends uuid;

@doc("人物グループID")
scalar personGroupId extends uuid;

@doc("開催先種別の値")
@enumExtensions
enum VenueKindValue {
  @doc("現地")
  Physical: 1,

  @doc("オンライン")
  Online: 2,
}

model EventVenueSummary {
  venueId: venueId;
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
  personGroupId: personGroupId;

  @doc("グループの表示用名称。別のグループと重複することがある")
  name: string;
}

@doc("楽曲披露の共演者。personGroup はグループとして全員で出演したときだけ持つ")
model PerformancePerson {
  personId: personId;
  name: string;
  creditName: performanceCreditName | null;
  personGroup: PerformancePersonGroup | null;
  orderNo: orderNo;
}

model SongPerformance {
  performanceId: performanceId;
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

  @doc("付いているタグ(タグの表示順)")
  tags: AttachedTag[];
}
