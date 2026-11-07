# Production Infrastructure

本番向けビルド定義の入口です
共通説明と比較表は `../README.md` を参照する

## この環境の差分

| 項目 | 値 |
|---|---|
| Cloud Build trigger | stg と同じ substitution key を prod 用の値で設定 |
| build arg `DEPLOY_ENV` | `production` |
| service / job 名 | `prod-*` |
| media youtube import job | `prod-media-youtube-import` |
| Viewer deploy job | `prod-viewer-deploy` |
| Viewer `SITE_NOINDEX` | 付けない |
| Viewer / Admin `APP_ENV` | `production` |
| admin-proxy deploy job | `prod-admin-proxy-deploy` |
| Admin 環境バッジ | 非表示 (`APP_ENV=production`) |
