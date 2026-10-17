table "song_taggings" {
  schema  = schema.db
  comment = "楽曲のタグ付け"

  column "song_id" {
    null    = false
    type    = binary(16)
    comment = "楽曲ID"
  }
  column "tag_id" {
    null    = false
    type    = binary(16)
    comment = "タグID"
  }

  primary_key {
    columns = [column.song_id, column.tag_id]
  }

  index "fk_song_taggings_tag_id" {
    columns = [column.tag_id]
  }

  foreign_key "fk_song_taggings_song_id" {
    columns     = [column.song_id]
    ref_columns = [table.songs.column.song_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_song_taggings_tag_id" {
    columns     = [column.tag_id]
    ref_columns = [table.tags.column.tag_id]
    on_delete   = RESTRICT
  }
}
