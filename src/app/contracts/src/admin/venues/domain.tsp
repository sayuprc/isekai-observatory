import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

@doc("開催先ID")
scalar venueId extends uuid;

@doc("開催先名")
@minLength(1)
@maxLength(255)
scalar venueName extends string;

@doc("開催先種別名")
@minLength(1)
scalar venueKindName extends string;

@doc("開催先種別の値")
@IsekaiObservatory.Shared.enumDescriptions
enum VenueKindValue {
  @doc("現地")
  Physical: 1,

  @doc("オンライン")
  Online: 2,
}

@example(#{ name: "現地", value: VenueKindValue.Physical })
model VenueKind {
  name: venueKindName;
  value: VenueKindValue;
}

@example(#{
  venueId: "0cc5f492-22fe-4f38-b702-8b2375bc0254",
  name: "会場サンプルA",
  kind: #{ name: "現地", value: VenueKindValue.Physical },
})
model Venue {
  venueId: venueId;
  name: venueName;
  kind: VenueKind;
}

@doc("開催先一覧の 1 行分")
model VenueSummary {
  ...Venue;

  @doc("この開催先を使っているイベントの件数")
  eventCount: int32;
}
