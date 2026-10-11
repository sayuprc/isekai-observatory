---
id: ADR-0028
status: accepted
superseded_by: null
applies_to: [admin, viewer, admin-proxy]
---

# TypeScript パッケージの整形は Prettier に任せ、ESLint と Stylelint は lint だけを担う

## Context

Admin と Viewer では ESLint (`@stylistic`) が JS/TS/Astro の整形を兼ね、Biome は JSON だけを整形していた。整形と lint の責務が混ざり、`max-len` の除外パターンのような調整が必要になっていた

`.astro` をテンプレートまで含めて整形できるツールは限られる。Biome の Astro 対応は experimental で、oxfmt は Astro に未対応である。Astro 公式の整形手段は Prettier + prettier-plugin-astro である

admin-proxy だけが Biome で lint と整形を行っており、contracts の Biome は実行されていなかった

## Decision

- Admin・Viewer・admin-proxy の整形は Prettier に一本化する
- Admin と Viewer は prettier-plugin-astro を使い、`.astro` も Prettier で整形する
- Prettier の設定は各パッケージの `prettier.config.mjs` に置き、共通の項目は揃える
- ESLint は lint だけを担い、`eslint-config-prettier` で整形系ルールを無効にする
- Stylelint は lint だけを担い、Prettier と衝突する整形系ルールを入れない
- Biome は使わない
- 行長は Prettier の `printWidth: 120` に任せ、`max-len` による上限チェックは行わない
- contracts の `.tsp` は引き続き `tsp format` で整形する

## Consequences

### Positive

- `.astro` を含むすべてのファイルが 1 つの formatter で同じ規則に揃う
- 整形の調整を ESLint のルール設定で行う必要がなくなる
- リポジトリの JS/TS の整形ツールが Prettier だけになる
- 将来 oxfmt が Astro に対応した場合、Prettier 互換のため移行しやすい

### Negative

- JSX やテンプレートの改行位置など、Prettier の既定から外れた整形は選べない
- Prettier はコメントを折り返さないため、120 字を超えるコメントを検出できない
- 各パッケージの `prettier.config.mjs` は重複しており、変更時はすべてを揃える必要がある
- 演算子を行頭に置く `experimentalOperatorPosition` は experimental で、Prettier の更新で挙動が変わりうる
