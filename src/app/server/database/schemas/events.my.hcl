table "events" {
  schema  = schema.db
  comment = "イベント"

  column "event_id" {
    null    = false
    type    = binary(16)
    comment = "活動ID"
  }
  column "title" {
    null    = false
    type    = varchar(255)
    comment = "タイトル"
  }
  column "title_lower" {
    null = true
    type = varchar(255)
    as {
      expr = "lower(`title`)"
      type = VIRTUAL
    }
    comment = "タイトル(小文字)"
  }
  column "description" {
    null    = false
    type    = text
    comment = "説明"
  }
  column "type" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "活動種別"
  }
  column "start_on" {
    null    = true
    type    = date
    comment = "開催開始日"
  }
  column "end_on" {
    null    = true
    type    = date
    comment = "開催終了日"
  }
  column "status" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "開催状態(通常・延期・中止)"
  }
  column "is_display" {
    null    = false
    type    = bool
    comment = "公開するか"
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
    columns = [column.event_id]
  }

  index "idx_events_title_lower" {
    columns = [column.title_lower]
  }
  index "idx_events_schedule" {
    columns = [column.start_on, column.end_on, column.event_id]
  }
  index "idx_events_type" {
    columns = [column.type]
  }
  index "idx_events_status" {
    columns = [column.status]
  }
}
