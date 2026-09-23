table "song_performances" {
  schema  = schema.db
  comment = "イベントでの楽曲披露"

  column "performance_id" {
    null    = false
    type    = binary(16)
    comment = "楽曲披露ID"
  }
  column "event_id" {
    null    = false
    type    = binary(16)
    comment = "イベントID"
  }
  column "song_id" {
    null    = false
    type    = binary(16)
    comment = "楽曲ID"
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
    comment  = "表示順"
  }
  column "is_display" {
    null    = false
    type    = bool
    default = true
    comment = "表示するか"
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
