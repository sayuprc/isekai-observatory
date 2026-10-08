---
id: ADR-0033
status: accepted
superseded_by: null
applies_to: [api, admin, viewer]
---

# API は種別や状態を値だけで返し、表示名は画面が持つ

## Context

ADR-0032 で Viewer API は種別や状態を数値だけで返すようにしたが、Admin API は `{ name, value }` の形で返したままだった

管理画面はフォームの選択肢として表示名を別に書いており、Viewer と同じく種別が増えても選択肢の追加漏れに気づけなかった

楽曲種別の一覧 API と画面は、表示名を見せるためだけにあった

## Decision

- Admin API と Viewer API は、種別・状態・役割・権限を TypeSpec の enum の値だけで返す
  - 表示名を返す `{ name, value }` の型は契約で使わない
  - フィールド名は `type` / `status` / `kind` / `formats` のように `Value` を付けない。リクエストもレスポンスも同じ名前にする
- 表示名は各画面が `Record<XxxValue, string>` の対応表として持つ
  - 一覧の表示とフォームや絞り込みの選択肢は同じ対応表から作る
- サーバーは表示名を持たない
- 楽曲種別の一覧 API と管理画面の楽曲種別画面は削除する
- 数値で分岐するときは、数値を直接書かず画面で定義した名前付きの定数を使う

ADR-0032 を置き換える

## Consequences

### Positive

- 種別が増えると各画面の型検査が失敗し、表示名や選択肢の追加漏れを防げる
- 表示の言い回しを API を変えずに画面の都合で決められる
- 契約とサーバーの presenter が単純になる

### Negative

- 表示名は管理画面と Viewer の両方にあり、変えるときは両方を直す必要がある
- 表示名を変えても、画面を再ビルドするまで反映されない
