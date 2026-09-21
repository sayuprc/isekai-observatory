---
id: ADR-0012
status: accepted
superseded_by: null
applies_to: [api, admin]
---

# CHECK 制約を使わない

## Context

dev 環境の TiDB ([ADR-0002](ADR-0002-use-tidb-in-production-and-mysql-in-development.md)) で実際に問題が起きた
CHECK 制約付きのテーブル作成はエラーにならないが、TiDB は CHECK を黙って無視するため制約が付かない
その結果、次の migration 実行時に Atlas が CHECK の欠落を差分として検出し、適用が壊れた

ローカルの MySQL では CHECK が機能してしまうため、この不一致は開発環境では検出できない

また Atlas のスキーマ差分検出において CHECK の式は DB 側の正規化表記と揺れやすく、
適用済みでも差分が出続ける問題も発生していた (#893)

行内の不変条件はドメイン層で既に保証している
(例: `release_tracks` の「song_id か title の少なくとも一方は必須」は
`Release\Domain\Models\Track::create` が検証する)

## Decision

スキーマ定義 (`src/app/server/database/schemas/`) で CHECK 制約を使わない
行内の不変条件はドメイン層 (Entity / ValueObject / Domain Service) で保証する

既存の唯一の CHECK (`release_tracks_song_id_title_at_least_one`) は削除した

## Consequences

### Positive

- 開発 (MySQL) と本番 (TiDB) でスキーマの挙動が一致する
- Atlas の差分検出が CHECK の表記ゆれで不安定になることがなくなる
- 不変条件の置き場所がドメイン層に一本化される

### Negative

- ドメイン層を経由しない書き込み (手動 SQL 等) では不変条件が DB レベルで守られない
