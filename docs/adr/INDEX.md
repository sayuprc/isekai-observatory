# ADR Index

<!-- このファイルは adr:index タスクにより自動生成されます。直接編集しないでください -->

| ID | Status | Title | Applies To |
|----|--------|-------|------------|
| ADR-0001 | accepted | バックエンドの実装に PHP/Laravel を採用する | [api, admin] |
| ADR-0002 | accepted | 本番 DB に TiDB、開発環境に MySQL を採用する | [api, admin] |
| ADR-0003 | accepted | マイグレーションツールに Atlas を採用する | [api, admin] |
| ADR-0004 | accepted | フロントエンドに Astro と SolidJS を採用する | [admin, viewer] |
| ADR-0005 | accepted | API コントラクトの定義に TypeSpec と OpenAPI Specification を採用する | [api, admin, viewer] |
| ADR-0006 | accepted | サーバーに ADOP アーキテクチャを採用する | [api, admin] |
| ADR-0007 | accepted | BFF の実装に Astro と Elysia.js を採用する | [admin, viewer] |
| ADR-0008 | accepted | タスクランナー・環境変数・ツール管理に mise を採用する | [api, admin, viewer] |
| ADR-0009 | accepted | 開発環境に Docker を採用する | [api, admin, viewer] |
| ADR-0010 | accepted | パッケージ管理に pnpm を採用する | [api, admin, viewer] |
| ADR-0011 | accepted | API の ORM を Eloquent から emonkak/orm へ移行する | [api] |
| ADR-0012 | accepted | CHECK 制約を使わない | [api, admin] |
| ADR-0013 | accepted | 業務エラーの表現を Result から例外へ移行する | [api, admin, viewer] |
| ADR-0014 | accepted | 入力形式検証を契約境界へ集約する | [api, admin] |
| ADR-0015 | accepted | 権利者の画像素材を保存・配信しない | [api, admin, viewer] |
| ADR-0016 | accepted | 管理認証は passkey のみとする | [api, admin] |
| ADR-0017 | accepted | passkey 紛失時はリカバリーコードで復旧する | [api, admin] |
| ADR-0018 | accepted | 管理ユーザー登録は招待トークン経由とする | [api, admin] |
| ADR-0019 | accepted | Release は MusicBrainz 型の階層と Release.formats を取る | [api, admin, viewer] |
| ADR-0020 | accepted | Media は 6 フィールドと MediaType 1 軸に閉じる | [api, admin, viewer] |
| ADR-0021 | accepted | Viewer API は Admin と分離した公開 read model とする | [api, viewer] |
| ADR-0022 | accepted | 監査ログは Support の明示記録とする | [api, admin] |
| ADR-0023 | accepted | 人物は Person に一本化し役割は関係の role で表す | [api, admin] |
| ADR-0024 | accepted | コンテナイメージの版は Dockerfile に直接書く | [api, admin, viewer] |
| ADR-0025 | accepted | 開催先は物理施設とオンライン配信プラットフォームを統合する | [api, admin, viewer] |
