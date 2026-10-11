# PHP コードレビューチェックリスト

規約の詳細は ADR-0006 / ADR-0013 / ADR-0014 と `docs/design-docs/subproject-boundaries.md` を正とする
ここではレビュー時に見る観点だけを列挙する

## 言語 & 基本

- [ ] 引数と戻り値に型宣言があるか
- [ ] PHP 8.5+ の機能が適切に使われているか

## エラーハンドリング (ADR-0013 / ADR-0014)

- [ ] 業務エラーが例外で表現されているか
- [ ] 入力形式ルールが TypeSpec / OpenApiValidator 側にあり、UseCase / Domain に漏れていないか
- [ ] VO が直接 `new` で、形式失敗を 4xx に握りつぶしていないか
- [ ] 例外 → HTTP が `ApiExceptionRenderer` に集約されているか

## アーキテクチャ (ADOP / ADR-0006)

- [ ] Domain / Application / Infrastructures の依存方向が守られているか
- [ ] Domain に Laravel / Eloquent 依存がないか
- [ ] UseCase が Repository Interface 経由で永続化しているか

## テスト

- [ ] Unit は Mockery、Integration / Feature は `DatabaseTestCase` か
- [ ] 種別に応じた php-*-test-creator の規約と整合しているか
