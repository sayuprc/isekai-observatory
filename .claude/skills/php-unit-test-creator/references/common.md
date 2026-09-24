# PHP テスト共通規約

Unit / Feature / Integration で共通するルール

## 必須

- `declare(strict_types=1);`
- テストメソッドは `#[Test]` アトリビュートを使う (`@test` や `test` プレフィックスは使わない)
- ドメインモデルの生成が必要なら `Tests\Support\Domain\EntityFactory`
- DB を使うテストは `EntityStore` も併用する

## 品質ゲート

- フォーマット: `mise run api:ecs:fix`
- 実行: `mise run api:test <作成したテストのパス>` (または各 skill 記載の `api:test:unit` / `feature` / `integration`)
