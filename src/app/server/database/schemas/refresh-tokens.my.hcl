table "refresh_tokens" {
  schema  = schema.db
  comment = "リフレッシュトークン"

  column "refresh_token_id" {
    null    = false
    type    = binary(16)
    comment = "リフレッシュトークンID"
  }
  column "admin_user_id" {
    null    = false
    type    = binary(16)
    comment = "管理ユーザーID"
  }
  column "token" {
    null    = false
    type    = varchar(255)
    comment = "トークン"
  }
  column "expired_at" {
    null    = false
    type    = datetime
    comment = "有効期限"
  }
  column "status" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "消費状態"
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
    columns = [column.refresh_token_id]
  }

  index "fk_refresh_tokens_admin_user_id" {
    columns = [column.admin_user_id]
  }

  foreign_key "fk_refresh_tokens_admin_user_id" {
    columns     = [column.admin_user_id]
    ref_columns = [table.admin_users.column.admin_user_id]
    on_delete   = CASCADE
  }
}
