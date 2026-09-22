---
id: ADR-0026
status: accepted
superseded_by: null
applies_to: [api, admin, viewer]
---

# 開催単位は Event とし Activity 集約を導入しない

## Context

ライブ、配信、展示は日時・開催先・延期・中止・セットリストを共有する一方、Release と Media は異なるライフサイクルと固有情報を持つ。これらを横断して「活動」と呼ぶ場面はあるが、共通 Activity 集約を置くと、まだ共通の振る舞いがない対象を一つの永続モデルへ束ねることになる。現在の開催集約を ActivityRecord と呼ぶ案も、永続集約より read model や記録 DTO に見える。

## Decision

- 開催単位の集約は `Event` と呼び、UI では「活動」と表示する
- `Activity` は Event / Release / Media をまとめて捉えるための用語に留め、契約、集約、共通テーブルとして導入しない
- Event / Release / Media はそれぞれ独立した集約として維持する
- 種類を横断する活動履歴が必要な場合は、独立した集約から組み立てる read model として設計する
- TypeScript の DOM `Event` との衝突を避けるため、公開契約の型名には `EventSummary` / `EventDetail` など役割を含める

## Consequences

### Positive

- 日時を伴う出来事と、活動全体を指す日常語を区別できる
- Event / Release / Media の異なる不変条件とライフサイクルを混在させずに済む
- 利用されない継承、共通テーブル、判別 union を作らずに済む
- Venue 仕様ですでに使っている Event の語と揃う

### Negative

- 種類横断の活動履歴は、複数の公開 read model を統合して組み立てる必要がある
- UI の「活動」とコード上の `Event` で語が一致しない
- フロントエンドでは DOM `Event` と区別できる型名を選ぶ必要がある
