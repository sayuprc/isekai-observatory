---
paths:
  - "src/notification/contract/**"
---

# Notify Contract 規約

## 実行環境

- MoonBit toolchain (`moon`) を使う
- 共有タスクの入口には `mise` を使う

## 構成

- `types.mbt`: `Notification` と `NotificationError`
- `parse.mbt`: アプリ通知 JSON の parse / validate

## 実装規約

- Discord 配達や Pub/Sub publish は持たない。契約と検証だけに閉じる
- 契約の詳細は `src/notification/contract/README.md` を Source of Truth とする
- `notify-publish` / `discord-notifier` は `moon.work` 経由でこのモジュールに依存する

## 検証

- `mise run notify-contract:check`
- 編集の最後に `moon info && moon fmt` を回す
