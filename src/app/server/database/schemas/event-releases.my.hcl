table "event_releases" {
  schema  = schema.db
  comment = "イベントリリース関連"

  column "event_id" {
    null    = false
    type    = binary(16)
    comment = "イベントID"
  }
  column "release_id" {
    null    = false
    type    = binary(16)
    comment = "リリースID"
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
    comment  = "表示順"
  }

  primary_key {
    columns = [column.event_id, column.release_id]
  }

  index "fk_event_releases_release_id" {
    columns = [column.release_id]
  }

  foreign_key "fk_event_releases_event_id" {
    columns     = [column.event_id]
    ref_columns = [table.events.column.event_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_event_releases_release_id" {
    columns     = [column.release_id]
    ref_columns = [table.releases.column.release_id]
    on_delete   = RESTRICT
  }
}
