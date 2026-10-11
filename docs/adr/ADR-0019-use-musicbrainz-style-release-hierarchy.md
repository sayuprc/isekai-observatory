---
id: ADR-0019
status: accepted
superseded_by: null
applies_to: [api, admin, viewer]
---

# Release は MusicBrainz 型の階層と Release.formats を取る

## Context

当初は「版違いは別 Release」とし、流通形態を単一属性で持っていた
同一作品の複数版・複合フォーマット (CD+DVD 等) を表現すると二重入力や見出しの誤用が起きる
ジャケット廃止後の代表色 (ADR-0015) も、版ごとに抽出元が異なる

## Decision

リリースドメインは次の階層とする

- `ReleaseGroup` (作品) → `Release` (版) → `Medium` (トラックリストの区切り) → `Track` (収録)
- Group と Release は別集約。Group 削除は傘下 Release がある場合は拒否する
- 提供形態は `Release.formats` (順序なし集合、子テーブル `release_formats`) が持つ
  Medium に format を載せない
- Medium は区切りであり、自由テキストの `name` (nullable) を持てる
- 代表色は `releases.color` に置く。Group の代表色は傘下版から導出する (ADR-0034 で代表色は持たないことにした)
- 管理画面の一覧・検索の主語は release-groups に置く

## Consequences

### Positive

- 版ごとの収録差と複数フォーマットを、二重登録なしに表現できる
- Medium の責務が「区切り」に戻り、見出し用の format 流用が不要になる
- 色の置き場が抽出元 (版) と一致する

### Negative

- 単層 Release より概念と UI が重い
- formats 空は入力検証エラーとし、呼び出し側が集合を必ず渡す必要がある
