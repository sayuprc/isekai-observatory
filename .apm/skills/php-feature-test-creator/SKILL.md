---
name: php-feature-test-creator
description: プロジェクトのフィーチャーテスト規約(API testing, Console testing, DatabaseTestCase)に従って、PHP のフィーチャーテストを作成します。「PHP のフィーチャーテストを作成して」や「API/コマンドのテストを書いて」と依頼された際に使用します
---

# PHP Feature Test Creator

## 共通規約

先に [../php-unit-test-creator/references/common.md](../php-unit-test-creator/references/common.md) を読む

## ワークフロー

1. **対象の特定**: API はパス・メソッド・ルート名。Console は `signature`・引数・オプション
2. **パス**: API は `src/app/server/tests/Feature/Api/`、Console は `src/app/server/tests/Feature/Console/`
3. **初期化**:
   - `Tests\Support\DatabaseTestCase` を継承する
   - 認証が必要な API は `Tests\Feature\Api\Admin\WithAuth` を使う
4. **記述**:
   - データ準備は EntityFactory + EntityStore
   - API は `postJson()` / `getJson()` 等。Console は `artisan()`
5. **実行**: `mise run api:test:feature` または `mise run api:test <path>`

## リファレンス

- [patterns.md](references/patterns.md)
