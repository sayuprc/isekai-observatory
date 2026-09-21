# リリース

閲覧サイト (viewer) の版数を上げて公開するまでの手順です

## 版数が指すもの

footer に出る `v1.0.0` は**公開サイトの版数**です

閲覧者に見える変更があったときだけ上げます
依存更新やリファクタなど、閲覧者から見て何も変わらない変更では上げません

repo に対する GitHub tag / Release とは別の系列です
サイト版数はタグを持たず、`src/app/viewer/package.json` と `src/app/viewer/src/pages/changelog.md` にのみ存在します

## Source of Truth

`src/app/viewer/package.json` の `version` です

このパッケージは `private: true` で publish されないため、`version` フィールドに他の用途がありません
`src/app/viewer/src/components/common/Footer.astro` がここから直接読みます

## 採番

| 種別 | 対象 |
| --- | --- |
| major | サイトの構成や見え方が大きく変わる (レイアウト刷新、ナビ再編) |
| minor | 新しいページや機能の追加 |
| patch | 既存の表示や挙動の修正 |

## 手順

1. `main` に入れる範囲の変更から、閲覧者に見えるものを拾う
2. 採番表に従って次の版数を決める
3. `src/app/viewer/package.json` の `version` を更新する
4. `src/app/viewer/src/pages/changelog.md` の先頭に節を追加する
5. `main` へ反映する

## changelog の書き方

新しい版数が上に来るように、`changelog.md` の先頭へ追加します

```markdown
## v1.1.0

- 楽曲一覧に絞り込みを追加しました
- リリース詳細の表示崩れを修正しました
```

見出しは版数のみで、日付は書きません
footer の版数と見比べれば、その変更が入っているかを閲覧者が判断できるためです

書くのは閲覧者に見える変更だけです
依存更新やリファクタは書きません

## 対象外

repo に対する GitHub Release の採番と発行手順は、`main` / `stg` を使った正式リリース運用を立ち上げるときに決めます
