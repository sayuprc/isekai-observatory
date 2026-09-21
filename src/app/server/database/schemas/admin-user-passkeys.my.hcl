table "admin_user_passkeys" {
  schema  = schema.db
  comment = "管理ユーザーのパスキー"

  column "admin_user_passkey_id" {
    null    = false
    type    = binary(16)
    comment = "管理ユーザーのパスキーID"
  }
  column "admin_user_id" {
    null    = false
    type    = binary(16)
    comment = "管理ユーザーID"
  }
  column "user_handle" {
    null    = false
    type    = varbinary(64)
    comment = "WebAuthn ユーザーハンドル"
  }
  column "name" {
    null    = false
    type    = varchar(255)
    comment = "パスキー名"
  }
  column "credential_id" {
    null    = false
    type    = varbinary(1023)
    comment = "WebAuthn クレデンシャル ID"
  }
  column "public_key" {
    null    = false
    type    = varbinary(4096)
    comment = "WebAuthn 公開鍵(COSE Key)"
  }
  column "aaguid" {
    null    = false
    type    = char(36)
    comment = "認証器 AAGUID"
  }
  column "transports" {
    null    = false
    type    = json
    comment = "認証器のトランスポート"
  }
  column "backup_eligible" {
    null    = true
    type    = bool
    comment = "バックアップ可能か"
  }
  column "backup_state" {
    null    = true
    type    = bool
    comment = "バックアップ済みか"
  }
  column "sign_count" {
    null     = false
    type     = bigint
    unsigned = true
    comment  = "署名カウンター"
  }
  column "last_used_at" {
    null    = true
    type    = datetime
    comment = "最終利用日時"
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
    columns = [column.admin_user_passkey_id]
  }

  index "admin_user_passkeys_admin_user_id_index" {
    columns = [column.admin_user_id]
  }

  index "admin_user_passkeys_user_handle_index" {
    columns = [column.user_handle]
  }

  index "admin_user_passkeys_credential_id_unique" {
    unique  = true
    columns = [column.credential_id]
  }

  foreign_key "fk_admin_user_passkeys_admin_user_id" {
    columns     = [column.admin_user_id]
    ref_columns = [table.admin_users.column.admin_user_id]
    on_delete   = CASCADE
  }
}
