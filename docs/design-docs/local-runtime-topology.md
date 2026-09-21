# Local Runtime Topology

ローカル開発時の実行構成をまとめる文書です

## Services

- `proxy`: Nginx がローカル TLS とホスト名を受け持つ
- `php`: Laravel API を実行する
- `mysql`: 開発用データベース
- `redis` / `redis-http`: Redis と HTTP 越しの接続口

## Local URLs

- `https://local.api.isekaijoucho.fan:<worktree ごとの HTTPS ポート>`
- `https://local.admin.isekaijoucho.fan:<worktree ごとの HTTPS ポート>`
- `https://local.isekaijoucho.fan:<worktree ごとの HTTPS ポート>`

## Worktree Isolation

- `git worktree add <path>` の後、その worktree で `mise run worktree:init` を実行してから `mise run up` や各 dev task を起動する
- `mise run worktree:init` が worktree ごとの Docker Compose project 名を生成する
- MySQL / Redis / redis-http / proxy / Astro dev server のポートは worktree ごとに自動採番する
- 採番した開発用変数は `mise.local.toml` の自動生成ブロックに保存する
- アプリ設定は `src/app/server/.env`, `src/app/server/.env.testing`, `src/app/admin/.env`, `src/app/viewer/.env` に書き戻す
- `src/app/server/.env` と `.env.testing` の `DB_PORT` は Docker network 向けの `3306` を維持し、host 側 task 用の `ATLAS_DB_PORT` を worktree ごとの MySQL 公開ポートへ更新する
- TLS 証明書 (`infra/local/docker/nginx/certs`, `infra/local/docker/php/certs/rootCA.pem`) は共有キャッシュ経由で worktree 間に複製する
- proxy は同一 worktree の `php` サービスと、その worktree に割り当てられた Admin / Viewer dev server へ接続し、Astro dev server の WebSocket も forward する
- 現在の割り当ては `mise run worktree:status` で確認する
- `compose.yaml` の fallback ポートは、`worktree:init` を使わない手動起動向けに従来の値を維持する

## Parallel Operation

- 原則として `contracts` / `server` / `admin` / `viewer` / 開発基盤の各変更は別 worktree で並列着手してよい
- 競合しやすい変更は「直列化」ではなく「owner を 1 worktree に固定して他 worktree が再取り込みする」ことで扱う
- `src/app/contracts` と生成物更新は波及範囲が広いため、生成責任を 1 worktree に寄せる
- `mise.toml` / `compose.yaml` / `infra/local/docker/` / lockfile の変更も専用 worktree に閉じ込め、他 worktree へ早めに取り込む
- 同じ Source of Truth を複数 worktree で同時編集する場合は、着手前に owner と取り込み順を Decision Log に残す

## Start Flow

1. ベースブランチを最新化する
2. タスクごとに `git worktree add <path> <branch>` で worktree を作る
3. 各 worktree で `mise run worktree:init` を実行する
4. 必要に応じて `mise run up`、`mise run admin:dev`、`mise run viewer:dev` を起動する
5. `mise run worktree:status` で URL とポートの衝突がないことを確認する
6. タスクごとの局所検証を回しながら実装を進める
7. 依存元の変更が進んだら、他 worktree はベースブランチまたは owner worktree の結果を再取り込みする

## Ownership Rules

- 1 つの Source of Truth に複数人が触れる場合は、変更を止めるのではなく owner worktree を先に決める
- `src/app/contracts` を触る worktree は、OpenAPI と各生成物の更新責任も持つ
- `mise.toml` / `compose.yaml` / `infra/local/docker/` / lockfile の変更は、専用 worktree を owner にする
- `src/app/server` / `src/app/admin` / `src/app/viewer` の独立実装は、同じ機能に関わっていても別 worktree に分けてよい
- 同じディレクトリの近接ファイルを複数 worktree で触る場合は、先にファイル ownership を分ける
- owner は変更を確定した時点で、他 worktree が再取り込みすべき基準 commit を共有する

## Conflict Hotspots

- `src/app/contracts` と `src/app/contracts/generated/`: 契約と生成物の owner を固定する
- `src/app/server/Generated/`, `src/app/admin/src/generated/`, `src/app/viewer/src/generated/`: 生成責任は contracts owner に寄せる
- `mise.toml`, `compose.yaml`, `infra/local/docker/`: 開発基盤 owner から早めに再取り込みする
- `src/app/pnpm-lock.yaml`, `composer.lock` などの lockfile: 更新 worktree を 1 つに固定する
- `.env` と `mise.local.toml`: `mise run worktree:init` の生成結果を尊重し、手編集で競合を作らない
- `infra/local/docker/nginx/certs` と `infra/local/docker/php/certs/rootCA.pem`: 最初の 1 worktree で生成した証明書を他 worktree へ複製する

## Integration Order

1. owner がいる変更から先に確定する
2. `src/app/contracts` を含む変更では、contracts owner が契約と生成物を先に更新する
3. `src/app/server` / `src/app/admin` / `src/app/viewer` の各 worktree は、その結果を再取り込みして実装を続ける
4. 基盤変更 worktree がある場合は、それを他 worktree へ先に流し込んで起動条件を揃える
5. 最終統合前に各 worktree で局所検証を通し、ベースブランチ上で代表検証を再実行する

依存関係が逆転する場合は、理由と取り込み順を実行計画の Decision Log に残す

## Minimum Validation

- `src/app/contracts`: `mise run contract:format:check`, `mise run contract:test`
- `src/app/server`: `mise run api:ecs`, `mise run api:phpstan`, `mise run api:test`
- `src/app/admin`: `cd src/app && bun --filter admin lint:check`, `cd src/app && bun --filter admin build`
- `src/app/viewer`: `cd src/app && bun --filter viewer lint:check`, `cd src/app && bun --filter viewer build`
- 開発基盤: `mise run worktree:status` と対象サービスの起動確認

## Validation Note

- 2026-04-26 時点で、本体 worktree に加えて 2 つの追加 worktree で `mise run worktree:status` を実行し、HTTPS / Admin / Viewer / MySQL / Redis / redis-http の各ポートが重複しないことを確認した
- 同日に 2 つの追加 worktree で `mise run up` を実行し、証明書同期込みで `proxy` / `php` / `mysql` / `redis` / `redis-http` が別 `COMPOSE_PROJECT_NAME` と別ポートで同時起動できることを確認した

## Example Split

- worktree A: `src/app/contracts` と生成物更新の owner
- worktree B: `src/app/server` の業務ロジック実装
- worktree C: `src/app/admin` または `src/app/viewer` の UI 実装
- worktree D: `mise.toml` / `compose.yaml` / `infra/local/docker/` のような基盤変更

この分け方では、A の変更を B/C が再取り込みし、D の変更を全 worktree が早めに取り込む

## Operation Rules

- 開発環境に関係する変数は `mise.toml` / `mise.local.toml` で管理する
- アプリに関係する変数は各サブプロジェクトの `.env` で管理する
- Docker image は worktree 間で共用し、コンテナ・ネットワーク・volume は `COMPOSE_PROJECT_NAME` で分離する

## Source Files

- `compose.yaml`
- `infra/local/docker/`
- `mise.toml`

起動構成や URL を変えるときは、近接する入口文書も同じ変更で更新します
