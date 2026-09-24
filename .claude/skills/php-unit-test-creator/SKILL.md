---
name: php-unit-test-creator
description: プロジェクトのテスト規約に従って、Laravel/PHP のユニットテストを作成します。「PHP のユニットテストを作成して」や「クラスのテストを書いて」と依頼された際に使用します
---

# PHP Unit Test Creator

## 共通規約

先に [references/common.md](references/common.md) を読む

## ワークフロー

1. **対象クラスの分析**: 名前空間、コンストラクタ依存、公開メソッド、戻り値の型を特定する
2. **パス**: `src/app/server/tests/Unit/` 配下に対象クラスの構造を模して置く
3. **初期化**:
   - `Tests\TestCase` を継承する
   - 依存は `MockInterface&ClassName` + `Mockery::mock()` で `setUp()` する
   - `getInstance()` で対象を `new` する
4. **記述**: 正常系と異常系。Mockery の expectation。例外は `expectException()`
5. **実行**: `mise run api:test:unit` または `mise run api:test <path>`

## リファレンス

- [patterns.md](references/patterns.md)
