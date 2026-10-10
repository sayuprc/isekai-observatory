import "../../shared";
import "../../packages/event";
import "../../packages/media";
import "../../packages/song";
import "../people/domain.tsp";
import "../person-groups/domain.tsp";
import "../release-groups/domain.tsp";
import "../venues/domain.tsp";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Event;
using IsekaiObservatory.Packages.Media;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Public;

@doc("出典")
model EventSource {
  displayName: eventSourceName;
  url: eventSourceUrl;
}

@doc("楽曲披露の共演者。personGroupId はグループとして全員で出演したときだけ持つ")
model CoVocalist {
  personId: personId;
  creditName: performanceCreditName | null;
  personGroupId: personGroupId | null;
}

@doc("楽曲披露。参照先の楽曲が非公開なら songId は null になり、songTitle だけが載る")
model SongPerformance {
  performanceId: performanceId;
  songId: songId | null;
  songTitle: title;

  @doc("共演者 (表示順)")
  coVocalists: CoVocalist[];
}

@doc("セットリスト項目。本人が歌唱しない演目は label だけを持つ")
model SetlistItem {
  label: setlistLabel | null;

  @doc("この項目で披露した楽曲披露の ID。メドレーでは複数になる")
  performanceIds: performanceId[];
}

@doc("公開イベント")
model Event {
  eventId: eventId;
  title: eventTitle;
  description: eventDescription;
  type: EventTypeValue;
  schedule: EventSchedule;
  status: EventStatusValue;

  @doc("開催先の ID (表示順)")
  venueIds: venueId[];

  @doc("関連する公開メディアの ID (表示順)")
  mediaIds: mediaId[];

  @doc("関連リリースの ID (表示順)。リリースとリリースグループがともに公開のものだけが載る")
  releaseIds: releaseId[];

  @doc("出典 (表示順)")
  sources: EventSource[];

  @doc("楽曲披露。セットリスト項目に属さないものも含む")
  performances: SongPerformance[];

  @doc("セットリスト (表示順)")
  setlist: SetlistItem[];
}
