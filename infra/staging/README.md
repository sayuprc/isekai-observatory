# Staging Infrastructure

ステージング向けビルド定義の入口です
共通説明と比較表は `../README.md` を参照する

## この環境の差分

| 項目 | 値 |
|---|---|
| Cloud Build trigger | staging 用の substitution value |
| build arg `DEPLOY_ENV` | `staging` |
| service / job 名 | `stg-*` |
| media youtube import job | `stg-media-youtube-import` |
| Viewer deploy job | `stg-viewer-deploy` |
| Viewer `SITE_NOINDEX` | `true` |
| Viewer / Admin `APP_ENV` | `staging` |
| admin-proxy deploy job | `stg-admin-proxy-deploy` |
| Admin 環境バッジ | 表示 (`APP_ENV=staging`) |
