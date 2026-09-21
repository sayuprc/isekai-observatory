table "admin_users" {
  schema  = schema.db
  comment = "管理ユーザー"

  column "admin_user_id" {
    null    = false
    type    = binary(16)
    comment = "管理ユーザーID"
  }
  column "name" {
    null    = false
    type    = varchar(255)
    comment = "管理者名"
  }
  column "email" {
    null    = false
    type    = varchar(255)
    comment = "メールアドレス"
  }
  column "role" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "ロール"
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
    columns = [column.admin_user_id]
  }

  index "admin_users_email_unique" {
    unique  = true
    columns = [column.email]
  }
}
