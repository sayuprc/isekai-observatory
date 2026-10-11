table "person_group_members" {
  schema  = schema.db
  comment = "人物グループのメンバー"

  column "person_group_id" {
    null    = false
    type    = binary(16)
    comment = "人物グループID"
  }
  column "person_id" {
    null    = false
    type    = binary(16)
    comment = "人物ID"
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
    comment  = "表示順"
  }

  primary_key {
    columns = [column.person_group_id, column.person_id]
  }

  index "fk_person_group_members_person_id" {
    columns = [column.person_id]
  }

  foreign_key "fk_person_group_members_person_group_id" {
    columns     = [column.person_group_id]
    ref_columns = [table.person_groups.column.person_group_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_person_group_members_person_id" {
    columns     = [column.person_id]
    ref_columns = [table.persons.column.person_id]
    on_delete   = CASCADE
  }
}
