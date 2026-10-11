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
MoonBit の install script 版は `FROM` ではなく、公開 datasource も無いため、Dockerfile に直書きしても Renovate は更新できない

## Decision

コンテナイメージの base 版と、Renovate が検知できるインストーラ版は Dockerfile に直接書く

- Cloud Build / mise task / GitHub Actions から、それらの版の `--build-arg` は渡さない
- ホストの開発ツール版は引き続き `mise.toml` の `[tools]` で管理する
- GitHub Actions の mise 本体の版は `.github/actions/setup-mise/action.yml` の `jdx/mise-action` の `version` に書く
- MoonBit の install script 版は `mise.toml` の `[vars].moonbit_version` を Source of Truth にする
- イメージ build と GitHub Actions は `tools/read-mise-value.sh` で読み、`--build-arg MOONBIT_VERSION` または install 引数へ渡す
- Dockerfile が `mise.toml` を COPY して `pnpm` / `atlas` を入れる経路は、mise manager で検知できるため残す

## Consequences

### Positive

- Renovate がイメージの `FROM` を直接更新できる
- 版の Source of Truth がビルド定義と同じファイルになる
- GitHub Actions の mise 本体の版は `jdx/mise-action` の `version` を github-actions manager が更新できる
- MoonBit の版を `mise.toml` の 1 箇所に揃えられる

### Negative

- ホストの `[tools]` とイメージの `FROM` は同じランタイムでも別ピンになる
- MoonBit の install script 版は公開 datasource が無いため、Renovate は更新しない
