---
id: ADR-0024
status: accepted
superseded_by: null
applies_to: [api, admin, viewer]
---

# コンテナイメージの版は Dockerfile に直接書く

## Context

ホストの開発ツールは `mise.toml` の `[tools]` で揃え、イメージの base 版も `[vars]` と `--build-arg` で共有していた
Renovate 導入後、Dockerfile に書いた `FROM` は検知できる一方、mise 経由の一元管理は dockerfile manager から見えない
ローカル Dockerfile に `ARG` のデフォルトがあっても、mise タスクが `--build-arg` で上書きするため、Renovate の更新がビルドに届かない

## Decision

コンテナイメージの base 版と、イメージ内で使うインストーラ版は Dockerfile に直接書く

- Cloud Build / mise task / GitHub Actions から版の `--build-arg` は渡さない
- ホストの開発ツール版は引き続き `mise.toml` の `[tools]` で管理する
- GitHub Actions の mise / MoonBit インストーラ版は `mise.toml` の `[vars]` に残す
- Dockerfile が `mise.toml` を COPY して `pnpm` / `atlas` を入れる経路は、mise manager で検知できるため残す

## Consequences

### Positive

- Renovate がイメージの `FROM` を直接更新できる
- 版の Source of Truth がビルド定義と同じファイルになる

### Negative

- ホストの `[tools]` とイメージの `FROM` は同じランタイムでも別ピンになる
- MoonBit の install script 版は `FROM` ではないため、Renovate の dockerfile manager だけでは更新できない
