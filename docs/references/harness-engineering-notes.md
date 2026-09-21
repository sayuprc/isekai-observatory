# Harness Engineering Notes

OpenAI の Harness Engineering 記事をこのリポジトリ向けに要約したメモです

## このリポジトリに持ち込む要点

- 入口文書は短い地図にして、詳細知識は別文書へ分ける
- エージェントが読めない情報は、存在しないのと同じとみなす
- 複雑な変更はローカルの作業計画で進め、拘束判断は ADR に残す
- 仕様が曖昧な変更は、先に `docs/specs/` を書いてから着手する
- 繰り返し出るレビューは、会話ではなく文書や設定へ昇格させる

## このリポジトリ向けの補足

- 共通入口は `docs/agent-map.md`。文書の置き場は `docs/INDEX.md`
- Agent 指示の配置 (rules / skills / hooks) は `docs/references/agent-instruction-placement.md`
- API の Source of Truth は `src/app/contracts` の TypeSpec であり、文書はその変更フローを補助する

## いまはまだやらないこと

- 巨大な `docs/agent-map.md` を育てること
- `AGENTS.md` に共通ルールや tool-specific な詳細ルールまで複写して二重管理すること
- まだ困っていない段階で文書の置き場をさらに増やすこと
- ドキュメントだけで解決できる問題に、先回りして専用ツールを増やすこと
