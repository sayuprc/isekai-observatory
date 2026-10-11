---
id: ADR-0022
status: accepted
superseded_by: null
applies_to: [api, admin]
---

# 監査ログは Support の明示記録とする

## Context

管理操作の追跡が必要だが、独立パッケージにするとターゲット種別参照で業務パッケージへの逆流依存が起きる
Eloquent やドメインイベントでの自動記録は「何を記録したか」がコードから読み取りにくい

## Decision

監査ログは独立パッケージにせず `Support` 配下に置く

- 対象 UseCase から `AuditLogRecorder` を明示呼び出しする
- 業務処理と同一トランザクションで記録する。Recorder 自身はトランザクションを張らない
- Eloquent イベントやドメインイベントによる自動記録はしない
- Entity を記録するときは原則として集約の `toArray()` を使う
  パスワードやトークン平文 / ハッシュなど機密を含むときだけ手フィルタする
- snapshot は固定スキーマ化せず JSON として保存する
- 監査ログ閲覧 API 自身は監査しない

## Consequences

### Positive

- 記録箇所が UseCase を読めば分かり、説明責任を果たしやすい
- 業務成功とログ欠損 (またはその逆) を同一 TX で防げる
- パッケージ間の逆流依存を避けられる

### Negative

- 新 UseCase 追加時に記録漏れが起きうる (自動記録より規律が要る)
- `toArray()` 依存のため、集約のシリアライズ形の変更がログ形状にも波及する
