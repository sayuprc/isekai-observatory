table "event_setlist_item_performances" {
  schema = schema.db

  column "setlist_item_id" {
    null = false
    type = binary(16)
  }
  column "performance_id" {
    null = false
    type = binary(16)
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
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
