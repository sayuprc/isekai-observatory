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
  column "title" {
    null    = true
    type    = varchar(255)
    comment = "トラック表示名(楽曲 1 件では楽曲名の上書き、メドレーでは表示名)"
  }

  primary_key {
    columns = [column.release_id, column.position, column.track_no]
  }

  foreign_key "fk_release_tracks_release_medium" {
    columns     = [column.release_id, column.position]
    ref_columns = [table.release_media.column.release_id, table.release_media.column.position]
    on_delete   = CASCADE
  }
}
