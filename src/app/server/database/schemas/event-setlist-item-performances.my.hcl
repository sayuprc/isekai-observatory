table "event_setlist_item_performances" {
  schema  = schema.db
  comment = "セットリスト項目の楽曲披露"

  column "setlist_item_id" {
    null    = false
    type    = binary(16)
    comment = "セットリスト項目ID"
  }
  column "performance_id" {
    null    = false
    type    = binary(16)
    comment = "楽曲披露ID"
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
    comment  = "表示順"
  }

  primary_key {
    columns = [column.setlist_item_id, column.performance_id]
  }

  index "fk_event_setlist_item_performances_performance_id" {
    columns = [column.performance_id]
  }

  foreign_key "fk_event_setlist_item_performances_item_id" {
    columns     = [column.setlist_item_id]
    ref_columns = [table.event_setlist_items.column.setlist_item_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_event_setlist_item_performances_performance_id" {
    columns     = [column.performance_id]
    ref_columns = [table.song_performances.column.performance_id]
    on_delete   = CASCADE
  }
}
