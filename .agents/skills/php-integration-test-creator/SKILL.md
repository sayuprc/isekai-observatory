---
name: php-integration-test-creator
description: プロジェクトのインテグレーションテスト規約に従って、PHP のインテグレーションテストを作成します。「PHP のインテグレーションテストを作成して」と依頼された際に使用します
---

# PHP Integration Test Creator

## 共通規約

先に [../php-unit-test-creator/references/common.md](../php-unit-test-creator/references/common.md) を読む

## ワークフロー

1. **対象の分析**: 依存関係と扱う集約・テーブルを確認する
2. **パス**: `src/app/server/tests/Integration/` 配下に対象の構造に合わせて置く
3. **初期化**:
   - `Tests\Support\DatabaseTestCase` を継承する
   - `getInstance()` は `$this->app->make(TargetClass::class)` で解決する
4. **記述**:
   - データ準備は EntityFactory + EntityStore
   - 戻り値・例外・`assertDatabaseHas()` やリポジトリで永続化を検証する
5. **実行**: `mise run api:test:integration` または `mise run api:test <path>`

## リファレンス

- [patterns.md](references/patterns.md)
