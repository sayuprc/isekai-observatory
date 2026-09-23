table "song_performance_persons" {
  schema = schema.db

  column "performance_id" {
    null = false
    type = binary(16)
  }
  column "person_id" {
    null = false
    type = binary(16)
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
  }
  column "credit_name" {
    null = true
    type = varchar(255)
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
