---
paths:
  - "src/app/viewer/**"
---

# 閲覧サイト規約

## 実行環境

- 依存管理とスクリプト実行には `bun` を使う
- `src/` は `admin` / `viewer` / `contracts` を束ねる Bun workspace のルート
- 共有タスクは `mise`、パッケージ固有タスクは `src/` で `bun --filter viewer <script>` として実行する

## 構成

- `src/app/viewer/src/pages/`: Astro のページとルーティング
- `src/app/viewer/src/layouts/`: ページレイアウト
- `src/app/viewer/src/components/`: 共通の UI コンポーネント
- `src/app/viewer/src/features/`: 機能単位の UI(`songs` / `releases` / `media` / `site-stats` など)
- `src/app/viewer/src/shared/`: 機能横断で使う部品
- `src/app/viewer/src/styles/`: CSS と CSS Modules
- `src/app/viewer/src/generated/`: OpenAPI から生成された API クライアント

## 実装規約

- ページ責務は `.astro` に保ち、対話的な UI は分離する
- API クライアントや型は `src/app/viewer/src/generated/` を Source of Truth とし、手動編集しない
- API shape を変える場合は `src/app/contracts` を更新してから `mise run viewer:generate` を使う
- コメントアウトされた UI は未提供セクションの雛形として意図的に残してある。削除せず保持する

## 検証

- `mise run viewer:check`
- `cd src && bun --filter viewer build`
