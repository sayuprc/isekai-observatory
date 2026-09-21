---
name: 'Notify Publish Instructions'
description: 'Use when editing the MoonBit notify-publish CLI in src/notification/publish. Covers module layout, publish-only responsibility, README as contract SoT, and validation.'
applyTo: "src/notification/publish/**"
---

# Notify Publish 規約

## 実行環境

- MoonBit toolchain (`moon`) を使う
- 共有タスクの入口には `mise` を使う

## 構成

- `payload.mbt`: `notify-contract` による検証と Pub/Sub PublishRequest body 生成
- `config.mbt`: env から project / topic / metadata host を読む
- `pubsub.mbt`: metadata token 取得と Pub/Sub publish
- `publish.mbt`: stdin → 検証 → publish の一連処理

## 実装規約

- Discord 配達や channel 振り分けは持たない。発信側の publish CLI に責務を閉じる
- アプリ通知 JSON の契約は `src/notification/contract/README.md` を Source of Truth とする
- 使い方・環境変数は `src/notification/publish/README.md` を参照する

## 検証

- `mise run notify-publish:check`
- 編集の最後に `moon info && moon fmt` を回す
