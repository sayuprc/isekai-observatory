---
id: ADR-0018
status: accepted
superseded_by: null
applies_to: [api, admin]
---

# 管理ユーザー登録は招待トークン経由とする

## Context

管理ユーザーを誰でも自己登録できると権限境界が崩れる
一方で運用者が password を仮発行すると ADR-0016 の passkey 一本化と矛盾する

## Decision

管理ユーザーの新規登録は CLI が発行する招待トークンを消費して行う

- `admin:invite` で email・role / permission を固定したトークンを発行する
  有効期限の既定は 7 日。平文は標準出力へ 1 行だけ出し、DB にはハッシュを保存する
- 発行時点では `admin_users` 行は存在しない。トークン行に email と権限を持ち、`admin_user_id` FK は持たない
- 登録 UI / API は email と平文トークンを受け、email で行を引き `verify` する
  bcrypt 由来の非決定的ハッシュのため、ハッシュ値だけで行検索しない
- トークン消費・ユーザー作成・passkey 保存・refresh / access token 発行は同一トランザクションに閉じる
  消費前に行ロック (`FOR UPDATE`) し、二重消費を防ぐ
- トークン不一致・期限切れ・消費済み・email 衝突などは外部に細分せず汎用エラーへ丸める
- Arkitect 上 `AdminUser\Domain` は `Auth\Domain` に依存できないため、
  トークン生成・ハッシュの interface / 実装は AdminUser 側に同等ロジックを置く

登録後の認証方式そのものは ADR-0016 に従う

## Consequences

### Positive

- 招待された email と権限だけが登録でき、自己登録や権限の持ち込みを防げる
- 失敗時の部分保存や二重消費を構造的に避けられる

### Negative

- トークン生成ロジックが Auth と AdminUser で重複する
- 未消費トークン同士の email 重複は許容するため、運用で古い招待の扱いを意識する必要がある
