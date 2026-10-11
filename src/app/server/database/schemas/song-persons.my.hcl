table "song_persons" {
  schema  = schema.db
  comment = "楽曲人物"

  column "song_id" {
    null    = false
    type    = binary(16)
    comment = "楽曲ID"
  }
  column "person_id" {
    null    = false
    type    = binary(16)
    comment = "人物ID"
  }
  column "role" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "役割"
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
    comment  = "表示順"
  }

  primary_key {
    columns = [column.song_id, column.person_id, column.role]
  }

  index "fk_song_persons_person_id" {
    columns = [column.person_id]
  }

  foreign_key "fk_song_persons_song_id" {
    columns     = [column.song_id]
    ref_columns = [table.songs.column.song_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_song_persons_person_id" {
    columns     = [column.person_id]
    ref_columns = [table.persons.column.person_id]
    on_delete   = RESTRICT
  }
}
