# Dev Infrastructure

開発環境向けビルド定義の入口です
共通説明と比較表は `../README.md` を参照する

## この環境の差分

| 項目 | 値 |
|---|---|
| Cloud Build trigger | dev 用の substitution value |
| build arg `DEPLOY_ENV` | `development` |
| service / job 名 | `dev-*` |
| media youtube import job | `dev-media-youtube-import` |
| Viewer deploy job | `dev-viewer-deploy` |
| Viewer `SITE_NOINDEX` | `true` |
| Viewer / Admin `APP_ENV` | `development` |
| admin-proxy deploy job | `dev-admin-proxy-deploy` |
| Admin 環境バッジ | 表示 (`APP_ENV=development`) |
