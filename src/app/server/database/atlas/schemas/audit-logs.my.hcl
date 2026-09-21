table "audit_logs" {
  schema  = schema.db
  comment = "監査ログ"

  column "audit_log_id" {
    null    = false
    type    = binary(16)
    comment = "監査ログID"
  }
  column "admin_user_id" {
    null    = false
    type    = binary(16)
    comment = "実行者の管理ユーザーID"
  }
  column "action" {
    null    = false
    type    = varchar(64)
    comment = "操作種別"
  }
  column "target_type" {
    null    = false
    type    = varchar(64)
    comment = "対象種別"
  }
  column "target_id" {
    null    = false
    type    = binary(16)
    comment = "対象ID"
  }
  column "snapshot" {
    null    = false
    type    = json
    comment = "変更後スナップショット"
  }
  column "created_at" {
    null    = false
    type    = datetime
    comment = "作成日時"
  }

  primary_key {
    columns = [column.audit_log_id]
  }

  index "fk_audit_logs_admin_user_id" {
    columns = [column.admin_user_id]
  }

  index "audit_logs_target_index" {
    columns = [column.target_type, column.target_id]
  }

  index "audit_logs_created_at_index" {
    columns = [column.created_at]
  }

  foreign_key "fk_audit_logs_admin_user_id" {
    columns     = [column.admin_user_id]
    ref_columns = [table.admin_users.column.admin_user_id]
    on_delete   = RESTRICT
  }
}
