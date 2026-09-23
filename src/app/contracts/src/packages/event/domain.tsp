import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Packages.Event;

@doc("活動ID")
@friendlyName("eventId")
scalar eventId extends uuid;

@doc("活動タイトル")
@minLength(1)
@friendlyName("eventTitle")
scalar eventTitle extends string;

@doc("活動説明")
@friendlyName("eventDescription")
scalar eventDescription extends string;

@doc("活動の出典表示名")
@minLength(1)
@friendlyName("eventSourceName")
scalar eventSourceName extends string;

@doc("活動の出典URL")
@format("uri")
@friendlyName("eventSourceUrl")
scalar eventSourceUrl extends string;

@doc("活動日")
@format("date")
@friendlyName("eventOn")
scalar eventOn extends string;

@doc("共演者としてのクレジット名")
@minLength(1)
@friendlyName("performanceCreditName")
scalar performanceCreditName extends string;

@doc("活動種別")
@friendlyName("EventTypeValue")
enum EventTypeValue {
  Live: 1,
  Stream: 2,
  Exhibition: 3,
  Other: 99,
}

@doc("活動の開催状態。通常・延期・中止を表す。予定・開催済みは開催時期から導出する")
@friendlyName("EventStatusValue")
enum EventStatusValue {
  Normal: 0,
  Postponed: 1,
  Cancelled: 2,
}

@doc("活動の開催時期。両方 null は日付未定、startOn のみは単日、両方指定は期間を表す")
model EventSchedule {
  startOn: eventOn | null;
  endOn: eventOn | null;
}
