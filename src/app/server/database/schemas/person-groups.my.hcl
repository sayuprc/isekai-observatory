table "person_groups" {
  schema  = schema.db
  comment = "人物グループ"

  column "person_group_id" {
    null    = false
    type    = binary(16)
    comment = "人物グループID"
  }
  column "name" {
    null    = false
    type    = varchar(255)
    comment = "人物グループ名"
  }
  column "name_lower" {
    null = true
    type = varchar(255)
    as {
      expr = "lower(`name`)"
      type = VIRTUAL
    }
    comment = "人物グループ名(小文字)"
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
    columns = [column.person_group_id]
  }

  index "idx_person_groups_name_lower" {
    columns = [column.name_lower]
  }

  index "person_groups_name_unique" {
    unique  = true
    columns = [column.name]
  }
}
