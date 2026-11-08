import "../../shared";
import "../../packages/event";
import "../../packages/media";
import "../../packages/song";
import "../releases";
import "../tags";
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
  note: note;
  type: EventTypeValue;
  schedule: EventSchedule;
  status: EventStatusValue;
  isDisplay: boolean;
  venues: RequestEventVenueLink[];
  media: RequestEventMediaLink[];
  releases: RequestEventReleaseLink[];
  sources: EventSource[];
  performances: RequestSongPerformance[];
  setlist: RequestSetlistItem[];
  tags: RequestTag[];
}

model EventCreateResponse {
  event: Event;
}

model EventUpdateRequest {
  title: eventTitle;
  description: eventDescription;
  note: note;
  type: EventTypeValue;
  schedule: EventSchedule;
  status: EventStatusValue;
  isDisplay: boolean;
  venues: RequestEventVenueLink[];
  media: RequestEventMediaLink[];
  releases: RequestEventReleaseLink[];
  sources: EventSource[];
  performances: RequestSongPerformance[];
  setlist: RequestSetlistItem[];
  tags: RequestTag[];
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

model RequestEventReleaseLink {
  releaseId: releaseId;
  orderNo: orderNo;
}

model RequestPerformancePerson {
  ...PickProperties<PerformancePerson, "personId" | "creditName" | "orderNo">;
  personGroupId: personGroupId | null;
}

model RequestSongPerformance {
  ...PickProperties<SongPerformance, "performanceId" | "songId" | "orderNo">;
  coVocalists: RequestPerformancePerson[];
}

model RequestSetlistItem {
  ...PickProperties<SetlistItem, "setlistItemId" | "orderNo" | "label">;
  performanceIds: performanceId[];
}

@doc("イベント一括更新の指定。付与・除去するタグと表示設定は、指定したものだけを反映する")
model EventBulkUpdateRequest {
  @doc("対象のイベントID。1 リクエストで受け付ける上限は 100 件")
  @minItems(1)
  @maxItems(100)
  eventIds: eventId[];

  @doc("付与するタグ。付いているものは何もしない")
  addTagIds?: tagId[];

  @doc("除去するタグ。付いていないものは何もしない")
  removeTagIds?: tagId[];

  @doc("表示設定の切り替え先")
  isDisplay?: boolean;
}

model EventBulkUpdateResponse {
  @doc("更新したイベントの件数")
  updatedCount: int32;
}
