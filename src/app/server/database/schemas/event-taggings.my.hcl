table "event_taggings" {
  schema  = schema.db
  comment = "イベントのタグ付け"

  column "event_id" {
    null    = false
    type    = binary(16)
    comment = "イベントID"
  }
  column "tag_id" {
    null    = false
    type    = binary(16)
    comment = "タグID"
  }

  primary_key {
    columns = [column.event_id, column.tag_id]
  }

  index "fk_event_taggings_tag_id" {
    columns = [column.tag_id]
  }

  foreign_key "fk_event_taggings_event_id" {
    columns     = [column.event_id]
    ref_columns = [table.events.column.event_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_event_taggings_tag_id" {
    columns     = [column.tag_id]
    ref_columns = [table.tags.column.tag_id]
    on_delete   = RESTRICT
  }
}
