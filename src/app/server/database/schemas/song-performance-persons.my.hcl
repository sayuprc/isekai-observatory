table "song_performance_persons" {
  schema  = schema.db
  comment = "楽曲披露の共演者"

  column "performance_id" {
    null    = false
    type    = binary(16)
    comment = "楽曲披露ID"
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
  column "credit_name" {
    null    = true
    type    = varchar(255)
    comment = "クレジット名(当日のユニット名など)"
  }

  primary_key {
    columns = [column.performance_id, column.person_id]
  }

  index "fk_song_performance_persons_person_id" {
    columns = [column.person_id]
  }

  foreign_key "fk_song_performance_persons_performance_id" {
    columns     = [column.performance_id]
    ref_columns = [table.song_performances.column.performance_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_song_performance_persons_person_id" {
    columns     = [column.person_id]
    ref_columns = [table.persons.column.person_id]
    on_delete   = RESTRICT
  }
}
