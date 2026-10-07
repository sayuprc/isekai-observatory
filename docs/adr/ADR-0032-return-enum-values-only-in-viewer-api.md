---
id: ADR-0032
status: accepted
superseded_by: null
applies_to: [api, viewer]
---

# Viewer API は種別や状態を数値だけで返し、表示名は Viewer が持つ

## Context

Viewer API は楽曲種別やイベント種別などを `{ name, value }` の形で返していた

表示名をサーバーと同じものにそろえるためだったが、Viewer は絞り込みの選択肢として表示名を別に書いていた

API から表示名が自動で届くため、種別が増えても絞り込みの選択肢の追加漏れに気づけなかった

種別の返し方も揃っておらず、数値だけ・表示名だけ・両方を返すものが混ざっていた

## Decision

- Viewer API は種別や状態を TypeSpec の enum の数値だけで返す
  - フィールド名は `typeValue` / `statusValue` / `kindValue` のように `Value` を付ける。複数なら `formatValues` とする
  - 表示名を返す `{ name, value }` の型は Viewer の契約で使わない
- 表示名は Viewer が `Record<XxxValue, string>` の対応表として持つ
  - 一覧の表示と絞り込みの選択肢は同じ対応表から作る
- 数値で分岐するときは、数値を直接書かず Viewer で定義した名前付きの定数を使う

## Consequences

### Positive

- 種別が増えると Viewer の型検査が失敗し、表示名や絞り込みの追加漏れを防げる
- 表示の言い回しを API を変えずに Viewer の都合で決められる
- 契約とサーバーの presenter が単純になる

### Negative

- 表示名を変えるときは、サーバー・管理画面・Viewer の各実装を直す必要がある
- 表示名を変えても、Viewer を再ビルドするまで反映されない
