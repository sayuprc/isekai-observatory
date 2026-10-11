table "release_formats" {
  schema  = schema.db
  comment = "リリース提供形態"

  column "release_id" {
    null    = false
    type    = binary(16)
    comment = "リリースID"
  }
  column "format" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "提供形態"
  }

  primary_key {
    columns = [column.release_id, column.format]
  }

  foreign_key "fk_release_formats_release_id" {
    columns     = [column.release_id]
    ref_columns = [table.releases.column.release_id]
    on_delete   = CASCADE
  }
}
