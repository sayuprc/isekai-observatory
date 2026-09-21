---
id: ADR-0010
status: accepted
superseded_by: null
applies_to: [api, admin, viewer]
---

# パッケージ管理に pnpm を採用する

## Context

プロジェクトの TypeScript 領域は `src/app/` 配下に admin、viewer、contracts の workspace を持つ
これらの依存関係はワークスペース横断で解決され、lockfile によって再現性を担保する必要がある

もともと Bun は Elysia を使うために採用しており、ランタイムやテスト、dev コマンドの実行環境としては高速で扱いやすい
一方で、Bun の lockfile を前提にした依存管理では Dependabot の通知が期待通りに届かない問題があった
依存更新の検知はセキュリティと保守性に関わるため、パッケージ管理では Dependabot が安定して扱える形式を優先する必要がある

pnpm は Bun より実行速度で劣るが、workspace、lockfile、catalog による依存バージョン管理が明示的で、Dependabot との相性もよい
このため、Bun は実行環境、pnpm はパッケージ管理という役割分担にする

## Decision

TypeScript 領域のパッケージ管理には pnpm を採用する

- 依存関係のインストールは `pnpm install` を使う
- lockfile は `src/app/pnpm-lock.yaml` を正とする
- workspace 定義は `src/app/pnpm-workspace.yaml` を正とする
- workspace 共通の依存バージョンは pnpm catalog で管理する
- Bun の lockfile は作成・更新しない

Bun はパッケージ管理には使わない
ただし、Bun 固有 API、Bun ランタイム、`bun:test`、dev コマンドなどの実行用途では Bun を継続して使用する
それらを廃止または移行する場合は、別の判断として扱う

## Consequences

### Positive

- Dependabot による依存更新通知を受け取りやすくなる
- パッケージ管理の責務が pnpm に集約され、依存更新の入口が明確になる
- `pnpm-lock.yaml` と `pnpm-workspace.yaml` を基準に、依存解決の再現性を保ちやすい
- pnpm catalog により、workspace 間で共有する依存バージョンを一箇所で管理できる
- install や CI の依存解決手順を明確にできる

### Negative

- Bun をパッケージ管理に使っていた既存のコマンドや文書は pnpm 前提へ更新する必要がある
- pnpm は Bun より実行速度で劣るため、パッケージ管理以外の実行用途まで pnpm に寄せないようにする必要がある
- Bun ランタイムを使う箇所が残るため、「パッケージ管理は pnpm、実行環境は Bun」という区別を保つ必要がある
- チームメンバーは pnpm workspace と catalog の使い方を理解する必要がある
