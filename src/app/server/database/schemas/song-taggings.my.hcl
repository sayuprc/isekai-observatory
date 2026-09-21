table "song_taggings" {
  schema  = schema.db
  comment = "楽曲タグ関連"

  column "song_id" {
    null    = false
    type    = binary(16)
    comment = "楽曲ID"
  }
  column "song_tag_id" {
    null    = false
    type    = binary(16)
    comment = "楽曲タグID"
  }

  primary_key {
    columns = [column.song_id, column.song_tag_id]
  }

  index "fk_song_taggings_song_tag_id" {
    columns = [column.song_tag_id]
  }

  foreign_key "fk_song_taggings_song_id" {
    columns     = [column.song_id]
    ref_columns = [table.songs.column.song_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_song_taggings_song_tag_id" {
    columns     = [column.song_tag_id]
    ref_columns = [table.song_tags.column.song_tag_id]
    on_delete   = RESTRICT
  }
}
