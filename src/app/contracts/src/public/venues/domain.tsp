import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Public;

@doc("開催先ID")
scalar venueId extends uuid;

@doc("開催先種別の値")
@enumExtensions
enum VenueKindValue {
  @doc("現地")
  Physical: 1,

  @doc("オンライン")
  Online: 2,
}

@doc("開催先。公開イベントから参照されているものだけが載る")
model Venue {
  venueId: venueId;
  name: string;
  kind: VenueKindValue;
}
