table "venues" {
  schema  = schema.db
  comment = "開催先"

  column "venue_id" {
    null    = false
    type    = binary(16)
    comment = "開催先ID"
  }
  column "name" {
    null    = false
    type    = varchar(255)
    comment = "開催先名"
  }
  column "name_lower" {
    null = true
    type = varchar(255)
    as {
      expr = "lower(`name`)"
      type = VIRTUAL
    }
    comment = "開催先名(小文字)"
  }
  column "kind" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "開催先種別"
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
    columns = [column.venue_id]
  }

  index "idx_venues_name_lower" {
    columns = [column.name_lower]
  }

  index "idx_venues_kind" {
    columns = [column.kind]
  }
}
