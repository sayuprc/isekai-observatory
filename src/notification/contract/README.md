# notify-contract

アプリ通知 JSON の共有契約 (MoonBit)

`notify-publish` と `discord-notifier` が同じ形を検証・パースするための Source of Truth

## 責務

- アプリ通知 JSON (`action` / `status` / `content?` / `embeds?`) の型と検証
- Discord 配達、Pub/Sub publish、Cloud Build 正規化は持たない

## 契約

```json
{
  "action": "viewer.deploy",
  "status": "succeeded",
  "content": "ok",
  "embeds": [
    {
      "title": "Viewer のデプロイが成功しました",
      "fields": [
        { "name": "site_url", "value": "https://example.com", "inline": true }
      ]
    }
  ]
}
```

- `action`: 処理単位の識別子。空文字は不可
- `status`: `started` / `succeeded` / `failed` / `alert` など。空文字は不可
- `content`: Discord webhook の本文 (任意)。空文字や未指定なら載せない
- `embeds`: Discord embed 配列 (任意)。空配列や未指定なら載せない
- `content` と非空の `embeds` のどちらか一方は必須

## 使い方

依存側は `moon.work` にこのモジュールを members として登録し、`isekai-observatory/notify-contract` を import する

```bash
cd src/notification/contract
moon test
```
