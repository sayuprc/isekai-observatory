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
  column "schedule_type" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "開催時期種別"
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
    null     = true
    type     = tinyint
    unsigned = true
    comment  = "開催状態(延期・中止のみ)"
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

table "event_venues" {
  schema = schema.db

  column "event_id" {
    null = false
    type = binary(16)
  }
  column "venue_id" {
    null = false
    type = binary(16)
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
  }

  primary_key {
    columns = [column.event_id, column.venue_id]
  }

  index "fk_event_venues_venue_id" {
    columns = [column.venue_id]
  }

  foreign_key "fk_event_venues_event_id" {
    columns     = [column.event_id]
    ref_columns = [table.events.column.event_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_event_venues_venue_id" {
    columns     = [column.venue_id]
    ref_columns = [table.venues.column.venue_id]
    on_delete   = RESTRICT
  }
}

table "event_media" {
  schema = schema.db

  column "event_id" {
    null = false
    type = binary(16)
  }
  column "media_id" {
    null = false
    type = binary(16)
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
  }

  primary_key {
    columns = [column.event_id, column.media_id]
  }

  index "fk_event_media_media_id" {
    columns = [column.media_id]
  }

  foreign_key "fk_event_media_event_id" {
    columns     = [column.event_id]
    ref_columns = [table.events.column.event_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_event_media_media_id" {
    columns     = [column.media_id]
    ref_columns = [table.media.column.media_id]
    on_delete   = RESTRICT
  }
}

table "event_sources" {
  schema = schema.db

  column "event_id" {
    null = false
    type = binary(16)
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
  }
  column "name" {
    null = false
    type = varchar(255)
  }
  column "url" {
    null = false
    type = text
  }

  primary_key {
    columns = [column.event_id, column.order_no]
  }

  foreign_key "fk_event_sources_event_id" {
    columns     = [column.event_id]
    ref_columns = [table.events.column.event_id]
    on_delete   = CASCADE
  }
}

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

table "event_setlist_items" {
  schema = schema.db

  column "setlist_item_id" {
    null = false
    type = binary(16)
  }
  column "event_id" {
    null = false
    type = binary(16)
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
  }
  column "label" {
    null = true
    type = varchar(255)
  }

  primary_key {
    columns = [column.setlist_item_id]
  }
  index "idx_event_setlist_items_event_id" {
    unique  = true
    columns = [column.event_id, column.order_no]
  }

  foreign_key "fk_event_setlist_items_event_id" {
    columns     = [column.event_id]
    ref_columns = [table.events.column.event_id]
    on_delete   = CASCADE
  }
}

table "event_setlist_item_performances" {
  schema = schema.db

  column "setlist_item_id" {
    null = false
    type = binary(16)
  }
  column "performance_id" {
    null = false
    type = binary(16)
  }
  column "order_no" {
    null     = false
    type     = int
    unsigned = true
  }

  primary_key {
    columns = [column.setlist_item_id, column.performance_id]
  }

  index "fk_event_setlist_item_performances_performance_id" {
    columns = [column.performance_id]
  }

  foreign_key "fk_event_setlist_item_performances_item_id" {
    columns     = [column.setlist_item_id]
    ref_columns = [table.event_setlist_items.column.setlist_item_id]
    on_delete   = CASCADE
  }
  foreign_key "fk_event_setlist_item_performances_performance_id" {
    columns     = [column.performance_id]
    ref_columns = [table.song_performances.column.performance_id]
    on_delete   = CASCADE
  }
}
