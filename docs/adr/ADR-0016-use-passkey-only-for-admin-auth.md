---
id: ADR-0016
status: accepted
superseded_by: null
applies_to: [api, admin]
---

# 管理認証は passkey のみとする

## Context

管理ユーザー認証は当初 email / password を前提にしていた
WebAuthn passkey へ移行するにあたり、password 互換を残すと「廃止したつもりで残る秘密」になり、契約・UI・DB のすべてが二系統になる

## Decision

管理ユーザーの認証は WebAuthn passkey に一本化する

- 登録・ログインはいずれも challenge 発行と検証を分ける `start` / `finish` ceremony とする
- `admin_users.password` カラムは持たない。ランダムパスワードの温存もしない
- WebAuthn 検証と credential 突合が成功するまで passkey 行・refresh token を更新しない
- 登録失敗の詳細 (token 不一致・期限切れ・email 衝突など) は外部に細分せず汎用エラーへ丸める
  入力形式違反だけ 422 とする

招待トークンによる登録導線は ADR-0018、紛失時の復旧は ADR-0017 を参照する

## Consequences

### Positive

- 認証の秘密が passkey に閉じ、password 互換の抜け道がなくなる
- 登録とログインで ceremony 形状が揃い、契約と BFF の責務が単純になる

### Negative

- password 利用者向けの移行パスは持たない (新規・再登録は passkey 前提)
- デバイス紛失時は ADR-0017 のリカバリーが必須になる
