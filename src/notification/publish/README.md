# notify-publish

通知 JSON を Pub/Sub topic へ publish する MoonBit native CLI

Discord 配達はしない。発信側から `{env}-discord-notify` へ載せる入口

## 責務

- stdin のアプリ通知 JSON を `notify-contract` で検証する
- GCE metadata から default SA の access token を取る
- `NOTIFICATION_TOPIC` へ `topics:publish` する

## 契約

アプリ通知 JSON の形は `src/notification/contract/README.md` を Source of Truth とする

## 使い方

```bash
export GOOGLE_CLOUD_PROJECT=my-project
export NOTIFICATION_TOPIC=dev-discord-notify

notify-publish <<'EOF'
{"action":"viewer.deploy","status":"succeeded","content":"ok"}
EOF
```

ローカルビルド:

```bash
cd src/notification/publish
moon test
moon build --target native --release
```

`moon.work` で `../contract` を members に含めている

## 環境変数

| 名前 | 用途 |
|---|---|
| `GOOGLE_CLOUD_PROJECT` | GCP project id |
| `NOTIFICATION_TOPIC` | publish 先 topic 名 |
| `GCE_METADATA_HOST` | metadata host (任意。Cloud が付ける値を使う) |
