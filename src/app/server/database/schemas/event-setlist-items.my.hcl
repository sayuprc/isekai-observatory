table "event_setlist_items" {
  schema  = schema.db
  comment = "セットリスト項目"

  column "setlist_item_id" {
    null    = false
    type    = binary(16)
    comment = "セットリスト項目ID"
  }
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
  column "label" {
    null    = true
    type    = varchar(255)
    comment = "表示名(本人が歌唱しない演目など)"
  }

  primary_key {
    columns = [column.setlist_item_id]
  }
  index "idx_event_setlist_items_event_id" {
    unique  = true
    columns = [column.event_id, column.order_no]
  }

  foreign_key "fk_event_setlist_items_event_id" {
    columns     = [column.event_id]
    ref_columns = [table.events.column.event_id]
    on_delete   = CASCADE
  }
}
