import "../../shared";
import "../../packages/event";
import "../../packages/media";
import "../../packages/song";
import "../media";
import "../persons";
import "../person-groups";
import "../releases";
import "../venues";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Event;
using IsekaiObservatory.Packages.Media;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Admin;

@doc("イベント出典")
model EventSource {
  displayName: eventSourceName;
  url: eventSourceUrl;
  orderNo: orderNo;
}

@doc("共演者が出演したグループ")
model PerformancePersonGroup {
  personGroupId: personGroupId;
  name: personGroupName;
}

@doc("楽曲披露の共演者。personGroup はグループとして全員で出演したときだけ持つ")
model PerformancePerson {
  personId: personId;
  name: personName;
  creditName: performanceCreditName | null;
  personGroup: PerformancePersonGroup | null;
  orderNo: orderNo;
}

@doc("イベントに紐づく楽曲披露")
model SongPerformance {
  performanceId: performanceId;
  songId: songId;
  songTitle: title;
  orderNo: orderNo;
  coVocalists: PerformancePerson[];
}

@doc("セットリストの 1 項目")
model SetlistItem {
  setlistItemId: setlistItemId;
  orderNo: orderNo;
  label: setlistLabel | null;
  performances: SongPerformance[];
}

@doc("イベントに関連づけたリリース(版)。isDisplay はリリースとリリースグループがともに公開のとき true")
model EventRelease {
  releaseId: releaseId;
  releaseGroupId: releaseGroupId;
  releaseGroupTitle: releaseGroupTitle;
  name: releaseName;
  releasedOn: releasedOn;
  isDisplay: boolean;
  formatValues: ReleaseFormatValue[];
}

@doc("イベント")
model Event {
  eventId: eventId;
  title: eventTitle;
  description: eventDescription;
  type: EventType;
  schedule: EventSchedule;
  status: EventStatus;
  isDisplay: boolean;
  venues: Venue[];
  media: Media[];
  releases: EventRelease[];
  sources: EventSource[];
  performances: SongPerformance[];
  setlist: SetlistItem[];
}

@doc("イベント一覧の 1 行分")
model EventSummary {
  eventId: eventId;
  title: eventTitle;
  type: EventType;
  schedule: EventSchedule;
  status: EventStatus;
  isDisplay: boolean;

  @doc("開催先名。登録順に並べる")
  venueNames: venueName[];

  @doc("楽曲披露の件数")
  performanceCount: int32;

  @doc("セットリスト項目の件数")
  setlistItemCount: int32;

  @doc("出典の件数")
  sourceCount: int32;
}
