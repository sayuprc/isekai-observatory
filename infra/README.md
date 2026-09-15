# Infrastructure

ローカル開発と環境別 Cloud Build / Dockerfile の入口です

## ディレクトリ

| パス | 役割 |
|---|---|
| `local/` | ローカル開発用コンテナ定義。詳細は `docs/design-docs/local-runtime-topology.md` |
| `development/` | 開発環境向け Cloud Build と Dockerfile |
| `staging/` | ステージング向け Cloud Build と Dockerfile |
| `production/` | 本番向け Cloud Build と Dockerfile |

環境固有の job 名・`APP_ENV`・初回設定の差分は下の比較表と、各ディレクトリの `README.md` を参照する

## 環境差分

| 項目 | development | staging | production |
|---|---|---|---|
| Cloud Build trigger | dev 用の substitution value | staging 用の substitution value | stg と同じ key を prod 用の値で設定 |
| service / job 名 | `dev-*` | `stg-*` | `prod-*` |
| media youtube import job | `dev-media-youtube-import` | `stg-media-youtube-import` | `prod-media-youtube-import` |
| Viewer deploy job | `dev-viewer-deploy` | `stg-viewer-deploy` | `prod-viewer-deploy` |
| Viewer `SITE_NOINDEX` | `true` | `true` | 付けない |
| Viewer / Admin `APP_ENV` | `development` | `staging` | `production` |
| admin-proxy deploy job | `dev-admin-proxy-deploy` | `stg-admin-proxy-deploy` | `prod-admin-proxy-deploy` |
| Admin 環境バッジ | 表示 | 表示 | 非表示 |

## Cloud Build (共通)

各環境の `cloudbuild/ci.yaml` が次を担う

- API / CLI / DB migrate / Admin / Viewer / admin-proxy / Discord Notifier のコンテナを build / push する
- Cloud Run service / job を deploy する
- build command は YAML に直接定義する

`ci.yaml` が受け取る主な substitution:

| substitution | 用途 |
|---|---|
| `_ARTIFACT_REPOSITORY` | Artifact Registry の repository |
| `_API_IMAGE` | API コンテナの image 名 |
| `_CLI_IMAGE` | CLI コンテナの image 名 |
| `_DB_MIGRATE_IMAGE` | DB migrate コンテナの image 名 |
| `_ADMIN_IMAGE` | Admin コンテナの image 名 |
| `_VIEWER_IMAGE` | Viewer コンテナの image 名 |
| `_ADMIN_PROXY_IMAGE` | admin-proxy コンテナの image 名 |
| `_DISCORD_NOTIFIER_IMAGE` | Discord Notifier コンテナの image 名 |
| `_PUBLIC_APP_URL` | Admin の `PUBLIC_APP_URL` 用 build arg |
| `_JOB_SERVICE_ACCOUNT` | Cloud Run jobs の service account |
| `_NOTIFY_SERVICE_ACCOUNT` | Discord Notifier の runtime service account |

コンテナイメージの base / インストーラ版は各 Dockerfile に直接書く。Renovate の dockerfile manager が検知できるようにする

## Dockerfiles (共通)

各環境の `docker/` に同名の Dockerfile がある。役割は環境横断で同じ

| Dockerfile | 役割 |
|---|---|
| `docker/api/Dockerfile` | Laravel API 用で FrankenPHP を使う |
| `docker/cli/Dockerfile` | artisan job 用で PHP CLI と Laravel application を含める |
| `docker/db-migrate/Dockerfile` | migration job 用で Atlas と schema 定義のみを含める |
| `docker/admin/Dockerfile` | Admin 用。build stage は `bun --filter admin build`、runtime stage は Bun slim image |
| `docker/viewer/Dockerfile` | Viewer deploy job 用。`viewer-deploy` を entrypoint にし、`notify-publish` を同梱する |
| `docker/admin-proxy/Dockerfile` | admin-proxy deploy job 用。`admin-proxy-deploy` を entrypoint にする |
| `docker/discord-notifier/Dockerfile` | MoonBit 製 Discord Notifier を native ビルドして載せる |

## 初回構築前提 (共通)

| 対象 | 設定内容 |
|---|---|
| Cloud Build trigger | 環境用の substitution value |
| Cloud Build service account | Artifact Registry、Cloud Run、Service Account User の必要権限 |
| API / Admin service と各 job の runtime env / secret | `ci.yaml` では定義しない。初回デプロイ前に別経路で設定する |
| DB migrate job | `DB_USERNAME` / `DB_PASSWORD` / `DB_HOST` / `DB_PORT` / `DB_DATABASE` |
| media youtube import job | DB 接続 env と `YOUTUBE_API_KEY` |
| Viewer deploy job | `API_URL` / `SITE_URL` / Cloudflare Worker 名 / account ID / `GOOGLE_CLOUD_PROJECT` / `NOTIFICATION_TOPIC` と `CLOUDFLARE_API_TOKEN` secret |
| admin-proxy deploy job | `ADMIN_PROXY_WORKER_NAME` / `ADMIN_PROXY_ORIGIN_URL` / `CLOUDFLARE_ACCOUNT_ID` と `CLOUDFLARE_API_TOKEN` / `PROXY_SHARED_SECRET` secret |
| Admin service | `PROXY_SHARED_SECRET` (admin-proxy と同じ secret) |

## 振る舞い (共通)

- Viewer deploy job は runtime env / secret を受け取り、Cloudflare Workers へ deploy する
- Viewer deploy 成功時は `notify-publish` で通知 JSON を publish する。`NOTIFICATION_TOPIC` / `GOOGLE_CLOUD_PROJECT` は infra 側で Job に設定し、Job SA に topic publish 権限を付ける
- admin-proxy deploy job は Cloud Build が `gcloud run jobs deploy --wait` で image 差し替えと実行完了まで行い、Cloudflare Workers へ deploy する
- Admin の `PUBLIC_APP_URL` は Cloud Build substitution の `_PUBLIC_APP_URL` を build arg として渡す

## 関連文書

| 文書 | 内容 |
|---|---|
| `docs/design-docs/local-runtime-topology.md` | ローカル実行構成 |
| `docs/operations/release.md` | 閲覧サイトの版数運用 |
| `src/notify-contract/README.md` | アプリ通知 JSON 契約 |
| `src/discord-notifier/README.md` | Discord Notifier |
| `src/notify-publish/README.md` | notify-publish |
