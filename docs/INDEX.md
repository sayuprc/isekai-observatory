# Docs Index

このディレクトリは、リポジトリ内で共有すべき知識を置くための記録システムです

入口文書は短く保ち、詳細は下位の文書へ分けます

## Maps

| 文書 | 内容 |
|---|---|
| `agent-map.md` | エージェント向けの共通地図 |
| `../ARCHITECTURE.md` | リポジトリ全体の地図 |
| `../FRONTEND.md` | 管理画面と閲覧サイトの UI 方針 |
| `../PLANS.md` | 作業計画 (ローカル) と ADR 昇格の入口 |
| `../infra/README.md` | ローカルと環境別インフラの入口 |

## Indexes

| 文書 | 内容 |
|---|---|
| `adr/INDEX.md` | ADR (Any Decision Record) |
| `design-docs/INDEX.md` | 継続的な設計原則 |
| `specs/INDEX.md` | プロダクトの現行仕様 |
| `references/INDEX.md` | 外部資料や参考実装の要点 |
| `operations/INDEX.md` | 繰り返し実行する運用手順 |
| `exec-plans/README.md` | ローカル作業計画の運用方法 |

## Placement Guide

| 置き場 | 内容 |
|---|---|
| `docs/adr/` | 将来の実装を拘束する判断 (Any Decision Record) |
| `docs/design-docs/` | 継続的に効く設計原則や信条 |
| `docs/specs/` | プロダクトの現行仕様 (用語・できること・できないこと・主な関係) |
| `docs/references/` | 外部記事、他 repo、仕様書の要約 |
| `docs/exec-plans/active/` | 作業中の実行計画 (gitignore。コミットしない) |
| `docs/operations/` | 繰り返し実行する運用手順 |

## Update Rule

コード変更が既存文書を古くした場合は、同じ変更で最も近い文書も更新する
仕様 (`docs/specs/`) が古くなったときも同じ
