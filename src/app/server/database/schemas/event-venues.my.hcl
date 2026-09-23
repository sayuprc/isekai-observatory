table "event_venues" {
  schema  = schema.db
  comment = "イベント開催先"

  column "event_id" {
    null    = false
    type    = binary(16)
    comment = "イベントID"
  }
  column "venue_id" {
    null    = false
    type    = binary(16)
    comment = "開催先ID"
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
    comment  = "表示順"
  }

  primary_key {
    columns = [column.event_id, column.venue_id]
  }

  index "fk_event_venues_venue_id" {
    columns = [column.venue_id]
  }

  foreign_key "fk_event_venues_event_id" {
    columns     = [column.event_id]
    ref_columns = [table.events.column.event_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_event_venues_venue_id" {
    columns     = [column.venue_id]
    ref_columns = [table.venues.column.venue_id]
    on_delete   = RESTRICT
  }
}
