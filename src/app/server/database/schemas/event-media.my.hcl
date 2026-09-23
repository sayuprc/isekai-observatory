table "event_media" {
  schema = schema.db

  column "event_id" {
    null = false
    type = binary(16)
  }
  column "media_id" {
    null = false
    type = binary(16)
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
  }

  primary_key {
    columns = [column.event_id, column.media_id]
  }

  index "fk_event_media_media_id" {
    columns = [column.media_id]
  }

  foreign_key "fk_event_media_event_id" {
    columns     = [column.event_id]
    ref_columns = [table.events.column.event_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_event_media_media_id" {
    columns     = [column.media_id]
    ref_columns = [table.media.column.media_id]
    on_delete   = RESTRICT
  }
}
