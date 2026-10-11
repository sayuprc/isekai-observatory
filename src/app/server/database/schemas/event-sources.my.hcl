table "event_sources" {
  schema  = schema.db
  comment = "イベント情報源"

  column "event_id" {
    null    = false
    type    = binary(16)
    comment = "イベントID"
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
    comment  = "表示順"
  }
  column "name" {
    null    = false
    type    = varchar(255)
    comment = "表示名"
  }
  column "url" {
    null    = false
    type    = text
    comment = "URL"
  }

  primary_key {
    columns = [column.event_id, column.order_no]
  }

  foreign_key "fk_event_sources_event_id" {
    columns     = [column.event_id]
    ref_columns = [table.events.column.event_id]
    on_delete   = CASCADE
  }
}
