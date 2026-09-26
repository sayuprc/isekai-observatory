---
id: ADR-0028
status: accepted
superseded_by: null
applies_to: [viewer]
---

# Viewer の整形は Prettier に任せ、ESLint と Stylelint は lint だけを担う

## Context

Viewer では ESLint (`@stylistic`) が JS/TS/Astro の整形を兼ね、Biome は JSON だけを整形していた。整形と lint の責務が混ざり、`max-len` の除外パターンのような調整が必要になっていた

`.astro` をテンプレートまで含めて整形できるツールは限られる。Biome の Astro 対応は experimental で、oxfmt は Astro に未対応である。Astro 公式の整形手段は Prettier + prettier-plugin-astro である

## Decision

- Viewer の整形は Prettier + prettier-plugin-astro に一本化する (`.astro` / `.ts` / `.tsx` / `.css` / `.json`)
- ESLint は lint だけを担い、`eslint-config-prettier` で整形系ルールを無効にする
- Stylelint は lint だけを担い、Prettier と衝突する整形系ルールを入れない
- Viewer では Biome を使わない
- 行長は Prettier の `printWidth: 120` に任せ、`max-len` による上限チェックは行わない

## Consequences

### Positive

- `.astro` を含むすべてのファイルが 1 つの formatter で同じ規則に揃う
- 整形の調整を ESLint のルール設定で行う必要がなくなる
- 将来 oxfmt が Astro に対応した場合、Prettier 互換のため移行しやすい

### Negative

- JSX やテンプレートの改行位置など、Prettier の既定から外れた整形は選べない
- Prettier はコメントを折り返さないため、120 字を超えるコメントを検出できない
- Admin・admin-proxy・contracts とは formatter が異なり、リポジトリ内に整形ツールが並存する
