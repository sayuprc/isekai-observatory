table "song_media_links" {
  schema  = schema.db
  comment = "楽曲メディア関連"

  column "song_id" {
    null    = false
    type    = binary(16)
    comment = "楽曲ID"
  }
  column "media_id" {
    null    = false
    type    = binary(16)
    comment = "メディアID"
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
    comment  = "表示順"
  }

  primary_key {
    columns = [column.song_id, column.media_id]
  }

  index "fk_song_media_links_media_id" {
    columns = [column.media_id]
  }

  foreign_key "fk_song_media_links_song_id" {
    columns     = [column.song_id]
    ref_columns = [table.songs.column.song_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_song_media_links_media_id" {
    columns     = [column.media_id]
    ref_columns = [table.media.column.media_id]
    on_delete   = RESTRICT
  }
}
