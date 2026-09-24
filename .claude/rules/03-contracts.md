---
paths:
  - "src/app/contracts/**"
---

# コントラクト規約

## 実行環境

- 依存管理には `pnpm` を使い、インストールは root から `mise run pnpm:install` を実行する
- スクリプト実行には `bun` を使う
- `src/` は `admin` / `viewer` / `contracts` を束ねる pnpm workspace のルート
- 契約まわりの共通タスクはリポジトリ root の `mise.toml` で `mise run contract:<task>` として実行する
- root の `contract:*` task は内部で `cd src && bun --filter contracts <script>` を使う
- package script を直接叩く場合は `cd src && bun --filter contracts <script>` を使う

## 構成

- `src/app/contracts/src/admin/main.tsp`: 管理画面向け API のエントリポイント
- `src/app/contracts/src/viewer/main.tsp`: 閲覧サイト向け API のエントリポイント
- `src/app/contracts/generated/oas/`: 生成された OpenAPI Specification
- `src/app/contracts/scripts/fix-enum-types.ts`: OpenAPI 生成後の補正スクリプト

## 実装規約

- TypeSpec を API 契約の Source of Truth とする
- 仕様変更時は生成物ではなく `.tsp` を編集する
- `src/app/contracts/generated/` は手動編集しない
- `src/app/contracts/tspconfig.yaml` の変更は出力先とエミッタ全体に影響するため慎重に扱う

## 検証

- `mise run contract:format:check`
- `mise run contract:test`
- 影響範囲に応じて `mise run contract:compile:admin` または `mise run contract:compile:viewer`
