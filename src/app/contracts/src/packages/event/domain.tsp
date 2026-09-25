import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Packages.Event;

@doc("イベントID")
@friendlyName("eventId")
scalar eventId extends uuid;

@doc("イベントタイトル")
@minLength(1)
@friendlyName("eventTitle")
scalar eventTitle extends string;

@doc("イベント説明")
@friendlyName("eventDescription")
scalar eventDescription extends string;

@doc("イベントの出典表示名")
@minLength(1)
@friendlyName("eventSourceName")
scalar eventSourceName extends string;

@doc("イベントの出典URL")
@format("uri")
@friendlyName("eventSourceUrl")
scalar eventSourceUrl extends string;

@doc("イベント日")
@format("date")
@friendlyName("eventOn")
scalar eventOn extends string;

@doc("楽曲披露ID")
@friendlyName("performanceId")
scalar performanceId extends uuid;

@doc("セットリスト項目ID")
@friendlyName("setlistItemId")
scalar setlistItemId extends uuid;

@doc("セットリスト項目の表示名")
@minLength(1)
@friendlyName("setlistLabel")
scalar setlistLabel extends string;

@doc("共演者としてのクレジット名")
@minLength(1)
@friendlyName("performanceCreditName")
scalar performanceCreditName extends string;

@doc("イベント種別")
@friendlyName("EventTypeValue")
enum EventTypeValue {
  Live: 1,
  Stream: 2,
  Exhibition: 3,
  Radio: 4,
  Other: 99,
}

@doc("イベントの開催状態。通常・延期・中止を表す。予定・開催済みは開催時期から導出する")
@friendlyName("EventStatusValue")
enum EventStatusValue {
  Normal: 1,
  Postponed: 2,
  Cancelled: 3,
}

@doc("イベントの開催時期。両方 null は日付未定、startOn のみは単日、両方指定は期間を表す")
model EventSchedule {
  startOn: eventOn | null;
  endOn: eventOn | null;
}
