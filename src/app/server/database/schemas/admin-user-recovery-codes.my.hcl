table "admin_user_recovery_codes" {
  schema  = schema.db
  comment = "管理ユーザーのリカバリーコード"

  column "admin_user_recovery_code_id" {
    null    = false
    type    = binary(16)
    comment = "管理ユーザーリカバリーコードID"
  }
  column "admin_user_id" {
    null    = false
    type    = binary(16)
    comment = "管理ユーザーID"
  }
  column "code" {
    null    = false
    type    = varchar(255)
    comment = "リカバリーコード(ハッシュ)"
  }
  column "status" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "消費状態"
  }
  column "used_at" {
    null    = true
    type    = datetime
    comment = "使用日時"
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
    columns = [column.admin_user_recovery_code_id]
  }

  index "admin_user_recovery_codes_admin_user_id_index" {
    columns = [column.admin_user_id]
  }

  foreign_key "fk_admin_user_recovery_codes_admin_user_id" {
    columns     = [column.admin_user_id]
    ref_columns = [table.admin_users.column.admin_user_id]
    on_delete   = CASCADE
  }
}
