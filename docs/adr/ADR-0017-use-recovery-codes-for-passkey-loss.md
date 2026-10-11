---
id: ADR-0017
status: accepted
superseded_by: null
applies_to: [api, admin]
---

# passkey 紛失時はリカバリーコードで復旧する

## Context

管理認証を passkey のみにしたあと (ADR-0016)、端末紛失でログイン不能になる
代替として password を戻すと一本化が崩れるため、ワンタイムの復旧手段が必要になる

## Decision

パスキー紛失時の復旧は、ログイン後に手動発行するリカバリーコードと専用 `Recovery` ceremony で行う

- 発行・再生成は認証済み API / 管理画面から行う。登録完了時の自動発行や CLI 発行はしない
- 既定は 10 個。平文は発行時に一度だけ提示し、DB にはハッシュのみを置く
- 復元成功時は既存 passkey を削除せず、新しい passkey を追加する
- コード消費は `RecoveryFinish` 成功時の同一トランザクションで確定する
  start では検証のみ行い、検証したコード id を ceremony state に束縛する
- `RecoveryStart` は実在 / 非実在・コード正否に依らず応答形状を揃え、ユーザー列挙を防ぐ
- リカバリーコードのドメインは `Auth` パッケージに置く

## Consequences

### Positive

- password を再導入せずに紛失復旧ができる
- 消費タイミングと id 束縛により、ceremony 失敗での無駄消費や別コードの過剰消費を防げる

### Negative

- 利用者が事前にリカバリーコードを保存していないと復旧できない
- start の列挙耐性のため、無効入力でも登録 ceremony 相当の応答が返り、実装とテストがやや重い
