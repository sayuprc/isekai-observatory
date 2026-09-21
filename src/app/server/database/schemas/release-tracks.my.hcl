table "release_tracks" {
  schema  = schema.db
  comment = "リリース収録曲"

  column "release_id" {
    null    = false
    type    = binary(16)
    comment = "リリースID"
  }
  column "position" {
    null     = false
    type     = int
    unsigned = true
    comment  = "媒体順"
  }
  column "track_no" {
    null     = false
    type     = int
    unsigned = true
    comment  = "曲順"
  }
  column "song_id" {
    null    = true
    type    = binary(16)
    comment = "楽曲ID"
  }
  column "title" {
    null    = true
    type    = varchar(255)
    comment = "トラック表示名(参照トラックでは楽曲名の上書き)"
  }

  primary_key {
    columns = [column.release_id, column.position, column.track_no]
  }

  index "fk_release_tracks_song_id" {
    columns = [column.song_id]
  }

  foreign_key "fk_release_tracks_release_medium" {
    columns     = [column.release_id, column.position]
    ref_columns = [table.release_media.column.release_id, table.release_media.column.position]
    on_delete   = CASCADE
  }
  foreign_key "fk_release_tracks_song_id" {
    columns     = [column.song_id]
    ref_columns = [table.songs.column.song_id]
    on_delete   = RESTRICT
  }
}
