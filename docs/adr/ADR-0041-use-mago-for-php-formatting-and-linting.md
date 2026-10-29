---
id: ADR-0041
status: accepted
superseded_by: null
applies_to: [api]
---

# PHP の整形と ecs 相当の lint を Mago に移す

## Context

PHP の整形は ECS (EasyCodingStandard) で行い、PSR-12 に加えて PHP-CS-Fixer / PHP_CodeSniffer のルールを個別に積み上げていた

ECS は Composer 依存としてコンテナ内で動くため、整形のたびに PHP コンテナが必要だった。CI でも整形チェックのためにイメージの取得とコンテナ起動を待っていた

Mago は Rust 製の単一バイナリで、mise からホストに入れて動かせる。formatter と linter を持ち、エージェントの編集後フックでは既に `mago lint` を使っていた

## Decision

- PHP の整形は `mago fmt` で行い、ECS を削除する
- ECS の fixer のうち整形以外のもの (strict comparison、`declare(strict_types=1)`、static closure など) は、対応する `mago lint` のルールで検出する
- linter で有効にするのは ECS の fixer に対応するルールだけとし、それ以外は明示的に無効にする
- ECS と同じく違反を 1 件でも残さないよう、`minimum-fail-level` は `note` にする
- 行長は formatter の `print-width = 120` に任せる
- 既存コードの改行位置は、`preserve-breaking-*` 系の設定でできるだけ保つ
- Mago の analyzer は使わず、静的解析は引き続き PHPStan で行う
- 設定の単一情報源は `src/app/server/mago.toml` とする

## Consequences

### Positive

- 整形と lint がホストで動き、コンテナを起動せずに実行できる
- CI の整形と lint のジョブが Docker イメージに依存しなくなる
- エージェントの編集後フックが、コンテナの起動状態に左右されなくなる

### Negative

- Mago はコード全体を再出力する formatter のため、ECS とは改行位置などが一致せず、移行時に全体の整形が必要になる
- ECS の PHPDoc 系 fixer (align / order など)、`OrderedInterfaces` / `OrderedTraits` / `OrderedTypes`、`YodaStyle`、`SimplifiedIfReturn` に相当するものはなく、これらは検査されなくなる
- `FullyQualifiedStrictTypesFixer` はグローバル名前空間のクラスに限った `no-fully-qualified-global-class-like` でしか代替できない
- Mago の更新で既定有効のルールが増えると、`mago.toml` に無効化を追記する必要がある
