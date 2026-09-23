table "event_setlist_items" {
  schema = schema.db

  column "setlist_item_id" {
    null = false
    type = binary(16)
  }
  column "event_id" {
    null = false
    type = binary(16)
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
  }
  column "label" {
    null = true
    type = varchar(255)
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
