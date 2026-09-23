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

@doc("活動の開催状態。通常開催は値を持たず、延期・中止のみ表現する")
@friendlyName("EventStatusValue")
enum EventStatusValue {
  Postponed: 1,
  Cancelled: 2,
}

@doc("活動の開催時期")
@friendlyName("EventScheduleTypeValue")
enum EventScheduleTypeValue {
  Undated: 1,
  Date: 2,
  DateRange: 3,
}

@doc("活動の開催時期。type に応じた項目のみを設定する")
model EventSchedule {
  type: EventScheduleTypeValue;
  startOn: eventOn | null;
  endOn: eventOn | null;
}
