table "media" {
  schema  = schema.db
  comment = "メディア"

  column "media_id" {
    null    = false
    type    = binary(16)
    comment = "メディアID"
  }
  column "title" {
    null    = false
    type    = varchar(255)
    comment = "タイトル"
  }
  // URL は日本語ドメインやパスをそのまま保持できるよう text のままにする
  // MySQL の index 長制約により text へ完全な unique 制約は張らず、重複はアプリケーション側で防ぐ
  column "url" {
    null    = false
    type    = text
    comment = "URL"
  }
  column "published_at" {
    null    = false
    type    = datetime
    comment = "公開日時"
  }
  column "type" {
    null     = false
    type     = tinyint
    unsigned = true
    comment  = "メディア種別"
  }
  column "is_display" {
    null    = false
    type    = bool
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
    columns = [column.media_id]
  }
}
