table "release_track_songs" {
  schema  = schema.db
  comment = "リリース収録曲の楽曲"

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
    null    = false
    type    = binary(16)
    comment = "楽曲ID"
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
    comment  = "収録曲内の順(メドレーの曲順)"
  }

  primary_key {
    columns = [column.release_id, column.position, column.track_no, column.song_id]
  }

  index "fk_release_track_songs_song_id" {
    columns = [column.song_id]
  }

  foreign_key "fk_release_track_songs_track" {
    columns     = [column.release_id, column.position, column.track_no]
    ref_columns = [table.release_tracks.column.release_id, table.release_tracks.column.position, table.release_tracks.column.track_no]
    on_delete   = CASCADE
  }
  foreign_key "fk_release_track_songs_song_id" {
    columns     = [column.song_id]
    ref_columns = [table.songs.column.song_id]
    on_delete   = RESTRICT
  }
}
