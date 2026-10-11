---
name: 'Documentation Instructions'
description: 'Use when updating README, ARCHITECTURE.md, FRONTEND.md, PLANS.md, ADRs, docs/specs/, references, local exec plans, or other Markdown docs. Covers repository-as-record-system conventions and document placement.'
applyTo: '**/*.md'
---

# 文書規約

- 長い手引きより短い地図を優先する。入口文書は要約に徹し、詳細は別文書へリンクする
- 文書の置き場は `docs/INDEX.md` の Placement Guide に従う
- 会話やレビューで繰り返し出るルールは、次回以降も使える形でリポジトリに残す
- 文書には事実、ディレクトリ名、コマンド、責務境界を書く。抽象論だけで終わらせない
- コード変更が文書を古くしたら、同じ変更で近接する文書も更新する
