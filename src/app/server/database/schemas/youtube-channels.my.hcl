table "youtube_channels" {
  schema  = schema.db
  comment = "インポート対象の YouTube チャンネル"

  // YouTube 側で一意かつ不変な ID をそのまま主キーにする
  column "channel_id" {
    null    = false
    type    = varchar(255)
    comment = "YouTube チャンネルID"
  }
  column "name" {
    null    = false
    type    = varchar(255)
    comment = "チャンネル名"
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
    columns = [column.channel_id]
  }
}
