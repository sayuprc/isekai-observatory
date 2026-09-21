table "admin_user_registration_token_permissions" {
  schema  = schema.db
  comment = "管理ユーザー登録トークン権限"

  column "admin_user_registration_token_id" {
    null    = false
    type    = binary(16)
    comment = "管理ユーザー登録トークンID"
  }
  column "permission" {
    null    = false
    type    = varchar(255)
    comment = "権限"
  }

  primary_key {
    columns = [column.admin_user_registration_token_id, column.permission]
  }

  foreign_key "fk_admin_user_registration_token_permissions_token_id" {
    columns     = [column.admin_user_registration_token_id]
    ref_columns = [table.admin_user_registration_tokens.column.admin_user_registration_token_id]
    on_delete   = CASCADE
  }
}
