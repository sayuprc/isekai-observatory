# Subproject Boundaries

継続的に参照するサブプロジェクトごとの責務境界をまとめる文書です

この文書のパスは、特記がなければリポジトリルート基準で書きます

## Contracts

- `src/app/contracts/`: TypeSpec による API 契約の Source of Truth
- `src/app/contracts/src/admin/main.tsp`: 管理画面向け契約の入口
- `src/app/contracts/src/viewer/main.tsp`: 閲覧サイト向け契約の入口
- `src/app/contracts/generated/`: 生成物。手動編集しない

## Server

- `src/app/server/`: PHP 8.5 / Laravel API サーバー
- `src/app/server/app/`: Laravel 固有の wiring
- `src/app/server/packages/{Package}/`: 業務ドメイン
- `src/app/server/database/`: Atlas によるスキーマ管理
- `src/app/server/tests/`: Unit / Integration / Feature テスト
- `src/app/server/Generated/`: OpenAPI 由来の生成コード

業務ロジックは ADR-0006 の ADOP を前提にし、`Domain`、`Application`、`Infrastructures`、`DebugInfrastructures` の境界を守ります

## Admin

- `src/app/admin/`: Astro / SolidJS / Elysia による管理画面
- `src/app/admin/src/pages/`: Astro ページ
- `src/app/admin/src/layouts/`: レイアウト
- `src/app/admin/src/components/`: SolidJS コンポーネント
- `src/app/admin/src/server/`: Elysia ベースの BFF / サーバー処理
- `src/app/admin/src/schemas/`: 入出力スキーマ
- `src/app/admin/src/generated/`: OpenAPI 由来の生成クライアント

UI 実装方針の詳細は `FRONTEND.md` を参照します

## Viewer

- `src/app/viewer/`: Astro / SolidJS による閲覧サイト
- `src/app/viewer/src/pages/`: ページ
- `src/app/viewer/src/layouts/`: レイアウト
- `src/app/viewer/src/components/`: UI コンポーネント
- `src/app/viewer/src/schemas/`: フロントエンド側のスキーマ
- `src/app/viewer/src/styles/`: スタイル

UI 実装方針の詳細は `FRONTEND.md` を参照します

## Notify Contract

- `src/notification/contract/`: アプリ通知 JSON の共有契約 (MoonBit)
- `Notification` 型と parse / validate を提供する
- `notify-publish` と `discord-notifier` が `moon.work` 経由で依存する
- Discord 配達や Pub/Sub publish は持たない

## Discord Notifier

- `src/notification/discord-notifier/`: MoonBit 製の Discord 通知配達サービス
- Pub/Sub push を受け、`action` を環境変数の routing で channel に引き当てて Discord Webhook へ投稿する
- 業務処理は持たない。振り分けは環境変数で行う
- アプリ通知 JSON の検証は `notify-contract` に委譲する
- 発信元は Discord を直接呼ばず、Pub/Sub に正規化済み JSON を publish する

## Notify Publish

- `src/notification/publish/`: MoonBit 製の通知 JSON → Pub/Sub publish CLI
- stdin のアプリ通知契約を `notify-contract` で検証し、`NOTIFICATION_TOPIC` へ publish する
- Discord 配達や channel 振り分けは持たない。bash Job などから使う発信側ツール
