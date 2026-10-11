# MoonBit コードレビューチェックリスト

規約の詳細は各パッケージ README と `docs/design-docs/subproject-boundaries.md` を正とする
ここではレビュー時に見る観点だけを列挙する

- `src/notification/contract/README.md`
- `src/notification/publish/README.md`
- `src/notification/discord-notifier/README.md`

`moon fmt` は check タスクが担保するので、フォーマット自体は見ない

## パッケージ境界

- [ ] `notify-contract` が配達や Pub/Sub publish を持っていないか
- [ ] `notify-publish` が Discord 配達や channel 振り分けを持っていないか
- [ ] `discord-notifier` が業務処理を持たず、振り分けが環境変数のままか
- [ ] アプリ通知 JSON の検証を `notify-contract` に委譲し、依存側で契約を再実装していないか
- [ ] 依存が `moon.work` 経由の `notify-contract` になっているか

## 契約

- [ ] 契約フィールドや必須条件の変更が `src/notification/contract/README.md` と実装で一致しているか
- [ ] Cloud Build / Cloud Run Job の正規化が notifier 側に閉じ、契約型へ不正な形を混ぜていないか

## テストと失敗の扱い

- [ ] parse / 正規化 / 振り分けの境界にテストがあるか
- [ ] 不正入力や検証失敗が握りつぶされず、機密がログに出ていないか
