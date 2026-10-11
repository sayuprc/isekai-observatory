---
name: code-reviewer
description: >
  isekai-observatory のコードレビュー。バックエンド(PHP/Laravel)、フロントエンド(Astro/SolidJS/TS)、
  MoonBit (notify-contract / notify-publish / discord-notifier) に対して、
  アーキテクチャ、セキュリティ、パフォーマンス、スタイルを確認する。
  コードのレビュー依頼、PR の確認、またはプロジェクト標準に対する実装の検証を求められたときに使う
---

# コードレビュー・スキル

## ワークフロー

### 1. コンテキストの分析

- 変更の範囲 (新機能、バグ修正、リファクタリング) を理解する
- 対象ファイルとその役割を特定する

### 2. 専門的なレビュー

対象に応じて参照する:

- **バックエンド (PHP)**: [references/php-review.md](references/php-review.md)
- **フロントエンド (SolidJS/Astro)**: [references/frontend-review.md](references/frontend-review.md)
- **MoonBit**: [references/moonbit-review.md](references/moonbit-review.md)
- **全般 (セキュリティ/パフォーマンス)**: [references/general-review.md](references/general-review.md)

規約の本文は ADR と `docs/` を正とする。レビュー用チェックリストだけを references に置く

### 3. 構造分析 (ADOP)

PHP サーバー側は ADR-0006 と `docs/design-docs/subproject-boundaries.md` に従うかを見る
MoonBit は ADOP 対象外。パッケージ境界は [references/moonbit-review.md](references/moonbit-review.md) を見る

- Domain が Application / Infrastructures に依存していないか
- Application が調整役でドメインロジックを抱えていないか
- Infrastructures が永続化や外部連携に閉じているか

### 4. フィードバック

#### 概要

簡潔な概要 (例: 「軽微な提案を含む LGTM」または「重大な問題を発見」)

#### 指摘事項

- **[重大]**: セキュリティ脆弱性、重大なバグ、深刻なアーキテクチャ違反。修正必須
- **[警告]**: パフォーマンス懸念、テスト不足、最適でないパターン。対処推奨
- **[提案]**: 可読性や軽微な改善

#### 具体例

変更を提案する場合は簡潔なコード例を添える
