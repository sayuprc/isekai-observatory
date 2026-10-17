table "release_group_taggings" {
  schema  = schema.db
  comment = "リリースグループのタグ付け"

  column "release_group_id" {
    null    = false
    type    = binary(16)
    comment = "リリースグループID"
  }
  column "tag_id" {
    null    = false
    type    = binary(16)
    comment = "タグID"
  }

  primary_key {
    columns = [column.release_group_id, column.tag_id]
  }

  index "fk_release_group_taggings_tag_id" {
    columns = [column.tag_id]
  }

  foreign_key "fk_release_group_taggings_release_group_id" {
    columns     = [column.release_group_id]
    ref_columns = [table.release_groups.column.release_group_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_release_group_taggings_tag_id" {
    columns     = [column.tag_id]
    ref_columns = [table.tags.column.tag_id]
    on_delete   = RESTRICT
  }
}
