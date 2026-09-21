table "release_media" {
  schema  = schema.db
  comment = "リリース媒体"

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
  column "name" {
    null    = true
    type    = varchar(255)
    comment = "媒体の表示ラベル"
  }

  primary_key {
    columns = [column.release_id, column.position]
  }

  foreign_key "fk_release_media_release_id" {
    columns     = [column.release_id]
    ref_columns = [table.releases.column.release_id]
    on_delete   = CASCADE
  }
}
