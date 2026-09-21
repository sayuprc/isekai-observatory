# ヰ世界観測所

ヰ世界情緒の情報を管理するためのモノレポです

## 構成

- アプリケーション本体: `src/app/`
- 通知基盤: `src/notification/`
- ローカル開発環境: `compose.yaml`, `infra/local/`, `mise.toml`
- 環境別インフラ定義: `infra/development/`, `infra/staging/`, `infra/production/`
- インフラ入口: `infra/README.md`
- 詳細: `ARCHITECTURE.md`

## セットアップ

前提:

- Docker / Docker Compose
- `mise`

最初のセットアップ:

1. `mise install`
2. `mise run setup`
3. 必要な追加タスクは `mise tasks` で確認する

`git worktree` を使うローカル開発運用は `docs/design-docs/local-runtime-topology.md` を参照する

TypeScript 関連: パッケージ管理は `src/app/` の pnpm workspace で行い、依存関係は `mise run pnpm:install` でインストールする。各 package script は Bun 実行環境で `cd src/app && bun --filter <package> <script>` として実行する

## ドキュメント

- `docs/agent-map.md`: エージェント向けの共通地図
- `ARCHITECTURE.md`: Source of Truth と変更ルート
- `docs/INDEX.md`: 文書の置き場所と一覧
