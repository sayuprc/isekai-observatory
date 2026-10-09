---
id: ADR-0034
status: accepted
superseded_by: null
applies_to: [api, admin, viewer]
---

# 種別の値と表示名は契約の enum を Source of Truth とする

## Context

ADR-0033 で API は種別や状態を値だけで返し、表示名は各画面が持つことにした

管理画面の表示名は、契約の enum メンバーの `@doc` と同じ文言を手書きで写していた

サーバーの domain enum も契約の値の手書きの写しで、監査ログ閲覧の権限が契約から漏れていたことがある

## Decision

- 種別・状態・役割・権限の値と表示名は、契約 (TypeSpec) の enum を Source of Truth とする
  - 表示名は enum メンバーの `@doc` に書き、OAS の `x-enum-descriptions` として出力する
- Admin API と Viewer API は値だけを返す
  - 表示名を返す `{ name, value }` の型は契約で使わない
  - フィールド名は `type` / `status` / `kind` / `formats` のように `Value` を付けない。リクエストもレスポンスも同じ名前にする
- 管理画面は `x-enum-descriptions` から `Record<XxxValue, string>` の対応表を生成して使う
  - 生成物は `src/app/admin/src/generated/enum-names.gen.ts` で、`mise run admin:generate` が作る
  - 一覧の表示とフォームや絞り込みの選択肢は同じ対応表から作る
- Viewer は閲覧者向けの言い回しを自分で決めるため、対応表を手で持つ
- サーバーの domain enum は契約の値と一致することをテストで確かめ、表示名は持たない

ADR-0033 を置き換える

## Consequences

### Positive

- 管理画面の表示名の写しがなくなり、種別を契約に足すだけで管理画面の表示と選択肢に反映される
- サーバーの domain enum と契約の値のずれをテストで検出できる

### Negative

- `@doc` の文言を変えると管理画面の表示も変わる
- 管理画面の生成に自前のスクリプトが加わる
- Viewer の表示名は契約と別に直す必要がある
