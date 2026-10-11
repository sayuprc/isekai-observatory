---
id: ADR-0021
status: accepted
superseded_by: null
applies_to: [api, viewer]
---

# Viewer API は Admin と分離した公開 read model とする

## Context

Viewer は Astro SSG の build 時取得が主用途である
Admin の認証付き search / get (offset pagination・内部状態付き) を流用すると、
公開画面に不要なフィールドとページング前提が混入する

## Decision

Viewer 向け API は Admin と契約・route・use case・presenter を別建てにする

- 公開対象のみを返す。`isDisplay` などの内部状態は契約に載せない
- 一覧 item を詳細相当の唯一の表示用 DTO とし、個別 get 口は持たない
- 件数増加時の分割取得は offset ではなく seek / cursor を前提にする
- サイト横断の公開件数は `SiteStats` など専用置き場に集約し、個別ドメインへ寄せない

## Consequences

### Positive

- SSG が 1 回の一覧取得でページ生成でき、N+1 的な詳細 fetch を避けられる
- Admin の管理用 shape 変更が Viewer 契約へ直撃しにくい

### Negative

- 同じ概念でも Admin / Viewer で実装が二重になる
- 個別 get が無いため、一覧に載らない ID の直接取得はできない (意図した公開境界)
