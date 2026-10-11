---
id: ADR-0020
status: accepted
superseded_by: null
applies_to: [api, admin, viewer]
---

# Media は 6 フィールドと MediaType 1 軸に閉じる

## Context

Media は type / format / platform / thumbnail など複数の分類軸を持ち、状態と表示導出が混在していた
platform と YouTube サムネイルを永続化すると、URL 変更との不整合や CHECK 制約 (ADR-0012 と衝突する案) を招く

## Decision

Media の永続状態は次の 6 フィールドに閉じる

- `mediaId` / `title` / `url` / `publishedAt` / `isDisplay` / `type`

分類は `MediaType` (Mv / AudioVideo / LiveStream / Short / Post / Other) の 1 軸のみとする
入力経路の未知・不正な type は `Other` にフォールバックする。DB 復元経路は正しい値が入っている前提とする

platform バッジと YouTube サムネイルは viewer が `url` から導出する
ドメイン・DB・契約・admin には platform / thumbnail / 旧 format を保存しない

## Consequences

### Positive

- 状態と表示導出の境界が明確になり、軸の重複が消える
- サムネイルを自前保存せずに済み、ADR-0015 の例外 (プラットフォーム CDN 直参照) と整合する

### Negative

- URL から導出できないホストではバッジ・サムネが弱くなる
- type の意味付けは運用と UI ラベルに依存し、細分類は `Other` に寄せやすい
