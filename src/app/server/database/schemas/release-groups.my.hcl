table "release_groups" {
  schema  = schema.db
  comment = "リリースグループ"

  column "release_group_id" {
    null    = false
    type    = binary(16)
    comment = "リリースグループID"
  }
  column "title" {
    null    = false
    type    = varchar(255)
    comment = "タイトル"
  }
  column "type" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "種別"
  }
  column "description" {
    null    = false
    type    = text
    comment = "説明"
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
    columns = [column.release_group_id]
  }
}
