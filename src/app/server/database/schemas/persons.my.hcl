table "persons" {
  schema  = schema.db
  comment = "人物"

  column "person_id" {
    null    = false
    type    = binary(16)
    comment = "人物ID"
  }
  column "name" {
    null    = false
    type    = varchar(255)
    comment = "人物名"
  }
  column "name_lower" {
    null = true
    type = varchar(255)
    as {
      expr = "lower(`name`)"
      type = VIRTUAL
    }
    comment = "人物名(小文字)"
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
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
    columns = [column.person_id]
  }

  index "idx_persons_name_lower" {
    columns = [column.name_lower]
  }

  index "persons_name_unique" {
    unique  = true
    columns = [column.name]
  }
}
