table "media_taggings" {
  schema  = schema.db
  comment = "メディアのタグ付け"

  column "media_id" {
    null    = false
    type    = binary(16)
    comment = "メディアID"
  }
  column "tag_id" {
    null    = false
    type    = binary(16)
    comment = "タグID"
  }

  primary_key {
    columns = [column.media_id, column.tag_id]
  }

  index "fk_media_taggings_tag_id" {
    columns = [column.tag_id]
  }

  foreign_key "fk_media_taggings_media_id" {
    columns     = [column.media_id]
    ref_columns = [table.media.column.media_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_media_taggings_tag_id" {
    columns     = [column.tag_id]
    ref_columns = [table.tags.column.tag_id]
    on_delete   = RESTRICT
  }
}
