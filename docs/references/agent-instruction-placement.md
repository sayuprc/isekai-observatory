# Agent Instruction Placement

Claude Code の記事「Steering Claude Code: CLAUDE.md files, skills, hooks, rules, subagents and more」から、このリポジトリで採用する配置基準をまとめる

- Source: https://claude.com/ja/blog/steering-claude-code-skills-hooks-rules-subagents-and-more
- Published: 2026-06-18

## 採用する方針

- `AGENTS.md` は短い入口に保つ。常時必要な地図だけを書き、詳細は `docs/agent-map.md` と下位文書へ逃がす
- パスに閉じる規約は path-scoped rules / instructions に置く。例: `src/app/server/**`, `src/app/admin/**`, `src/app/contracts/**`, `src/app/viewer/**`, `src/notification/contract/**`, `src/notification/discord-notifier/**`, `src/notification/publish/**`
- 手順として再利用する作業は skill に置く。例: PR 作成、コードレビュー、PHP テスト作成、ローカル exec plan と ADR 昇格
- main session を汚す調査や文書整理は subagent に寄せる。例: `docs-curator`
- 確実に実行したい処理は hook に置く。例: 編集後の近接 lint、保護対象 config の編集ブロック、contracts 変更後の stop verify

## このリポジトリの配置

- 常時入口: `AGENTS.md`, `docs/agent-map.md`
- Cursor path rules: `.cursor/rules/*.mdc`
- APM instructions: `.apm/instructions/*.instructions.md`
- Skills の Source of Truth: `.apm/skills/*/SKILL.md`
- Skills の配布先 (Cursor / Codex 等): `.agents/skills/` (`.apm/skills` から同期する)
- Subagents: `.cursor/agents/`, `.codex/agents/`
- Deterministic hooks: `tools/hooks/`, `.cursor/hooks.json`, `.codex/hooks.json`

## 判断基準

- すべての作業で必要な事実だけを入口文書に置く
- 特定ディレクトリでだけ必要な規約は path scope 付きの rules / instructions に置く
- 30 行程度以上の手順、チェックリスト、ロールプレイは入口文書ではなく skill に置く
- 「必ず実行する」「絶対にブロックする」はプロンプトではなく hook や permission で担保する
- 個人設定や一時的な好みは project-level の文書へ入れない
- 新しい agent 向けファイルを追加したら、同じ知識が複数の常時入口に重複していないか確認する

## この記事から採用しないこと

- 独自 output style は追加しない。コーディング時の既定挙動を壊すリスクがあるため
- project-level の入口文書へ個人の応答スタイルを追加しない
- guardrail を自然言語だけで表現しない。必要なら hook / permission / linter へ移す
