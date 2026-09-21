table "releases" {
  schema  = schema.db
  comment = "リリース"

  column "release_id" {
    null    = false
    type    = binary(16)
    comment = "リリースID"
  }
  column "release_group_id" {
    null    = false
    type    = binary(16)
    comment = "リリースグループID"
  }
  column "name" {
    null    = false
    type    = varchar(255)
    comment = "版名"
  }
  column "released_on" {
    null    = false
    type    = date
    comment = "発売日"
  }
  column "description" {
    null    = false
    type    = text
    comment = "説明"
  }
  column "color" {
    null    = false
    type    = varchar(7)
    comment = "代表色"
  }
  column "is_display" {
    null    = false
    type    = bool
    comment = "表示するか"
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
    default  = 1
    comment  = "表示順"
  }
  column "created_at" {
    null    = false
    type    = datetime
    comment = "作成日時"
  }
  column "updated_at" {
    null    = false
    type    = datetime
    comment = "更新日時"
  }

  primary_key {
    columns = [column.release_id]
  }

  index "fk_releases_release_group_id" {
    columns = [column.release_group_id]
  }

  foreign_key "fk_releases_release_group_id" {
    columns     = [column.release_group_id]
    ref_columns = [table.release_groups.column.release_group_id]
    on_delete   = RESTRICT
  }
}
