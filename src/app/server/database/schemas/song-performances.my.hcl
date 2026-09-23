table "song_performances" {
  schema  = schema.db
  comment = "活動での楽曲披露"

  column "performance_id" {
    null = false
    type = binary(16)
  }
  column "event_id" {
    null = false
    type = binary(16)
  }
  column "song_id" {
    null = false
    type = binary(16)
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
  }
  column "is_display" {
    null    = false
    type    = bool
    default = true
  }
  column "created_at" {
    null = false
    type = datetime
  }
  column "updated_at" {
    null = false
    type = datetime
  }

  primary_key {
    columns = [column.performance_id]
  }
  index "idx_song_performances_event_id" {
    columns = [column.event_id, column.order_no]
  }
  index "idx_song_performances_song_id" {
    columns = [column.song_id]
  }

  foreign_key "fk_song_performances_event_id" {
    columns     = [column.event_id]
    ref_columns = [table.events.column.event_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_song_performances_song_id" {
    columns     = [column.song_id]
    ref_columns = [table.songs.column.song_id]
    on_delete   = RESTRICT
  }
}
