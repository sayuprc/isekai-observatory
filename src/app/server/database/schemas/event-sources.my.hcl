table "event_sources" {
  schema = schema.db

  column "event_id" {
    null = false
    type = binary(16)
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
  }
  column "name" {
    null = false
    type = varchar(255)
  }
  column "url" {
    null = false
    type = text
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
