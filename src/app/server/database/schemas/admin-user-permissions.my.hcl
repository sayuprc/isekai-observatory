table "admin_user_permissions" {
  schema  = schema.db
  comment = "管理ユーザー権限"

  column "admin_user_id" {
    null    = false
    type    = binary(16)
    comment = "管理ユーザーID"
  }
  column "permission" {
    null    = false
    type    = varchar(255)
    comment = "権限"
  }

  primary_key {
    columns = [column.admin_user_id, column.permission]
  }

  foreign_key "fk_admin_user_permissions_admin_user_id" {
    columns     = [column.admin_user_id]
    ref_columns = [table.admin_users.column.admin_user_id]
    on_delete   = CASCADE
  }
}
