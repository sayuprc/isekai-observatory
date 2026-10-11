---
name: exec-plan
description: >
  実装タスクの作業計画の作成と運用。要件が固まったら docs/exec-plans/active/ にローカル計画を書き、「計画作成 → 実装 → レビュー → 修正」のループで進める。計画ファイルはコミットしない。将来を拘束する判断は ADR に書く。ユーザーが「exec-plan で」「計画を作って」「/exec-plan」と明示した場合、または複数セッションにまたがる実装タスクで手順の整理が必要なときに使う
---

# Exec Plan

計画書の作成・更新・完了処理だけを責務とする。実装はメインループ、レビューは `code-reviewer` に委ねる

## 規範の参照先

- いつ書くか / ADR との境界: `PLANS.md`
- 置き場と運用: `docs/exec-plans/README.md`
- 見出し: `docs/exec-plans/template.md`

## 前提

要件が曖昧なうちは計画を書かない。先に `/grilling`(または `/grill-with-docs`)で固めてから落とす

## ワークフロー

### 1. 計画作成

- `template.md` に従い `docs/exec-plans/active/YYYYMMDD-<slug>.md` を作成する
- Steps をユーザーに提示し、承認を得てから実装に進む

### 2. 実装

- Status を `in-progress` にし、Steps を上から実行する
- 計画と実態が食い違ったら更新する。拘束判断は ADR へ昇格する
- 計画ファイルを stage / commit しない

### 3. レビューと修正

- `code-reviewer` でレビューし、修正 → 再レビューを必要なだけ繰り返す

### 4. 完了

- Validation を埋め、拘束判断が ADR になっていることを確認する
- 計画ファイルは削除してよい (履歴ディレクトリには残さない)
