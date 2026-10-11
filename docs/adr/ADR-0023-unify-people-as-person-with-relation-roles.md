---
id: ADR-0023
status: accepted
superseded_by: null
applies_to: [api, admin]
---

# 人物は Person に一本化し役割は関係の role で表す

## Context

`Creator` と `Performer` を分けていると同一人物の二重登録が起き、楽曲側も複数テーブルで役割を持っていた
互換レイヤを残すと削除の完了条件が曖昧になる

## Decision

人物マスタは `Person` のみとする

- `Creator` / `Performer` の互換 API・画面・テーブルは残さない
- 楽曲との関係は `personId` + `role` (+ `orderNo`) を持つ単一の関係テーブルに統合する
- 公開契約も分割配列ではなく `personId` + `role` 構造に揃える
- role 体系は当面 song 文脈の Enum に閉じ、人物一般の role 体系へは広げない
- 管理画面の人物導線は `/persons` のみとする

## Consequences

### Positive

- 同一人物の重複登録を構造的に防げる
- 役割追加時にテーブルを増やさなくてよい

### Negative

- 既存の creator / performer 前提クライアントは破壊的変更になる
- song 以外の文脈で人物役割が必要になったとき、Enum / 関係モデルの再設計が要る
