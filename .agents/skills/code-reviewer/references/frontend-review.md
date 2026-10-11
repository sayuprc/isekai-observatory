# フロントエンド コードレビューチェックリスト (Astro + SolidJS + TS)

規約の詳細は `FRONTEND.md` と `docs/design-docs/subproject-boundaries.md` を正とする
ここではレビュー時に見る観点だけを列挙する

## フレームワーク & コンポーネント
- [ ] インタラクティブなコンポーネントに **SolidJS** を使用しているか
- [ ] CSS クラスの指定に `className` ではなく `class` を使用しているか(SolidJS の規約)
- [ ] コンポーネントが関数型であり、Props や State に TypeScript が使用されているか

## スタイリング (Tailwind CSS v4 + DaisyUI v5)
- [ ] Tailwind CSS のユーティリティクラスを使用しているか
- [ ] UI の一貫性のために DaisyUI のコンポーネント/クラスを適切に活用しているか
- [ ] 絶対に必要な場合を除き、カスタム CSS を避けているか

## API & データフェッチ
- [ ] バックエンド API との通信に `openapi-fetch` を使用しているか
- [ ] `src/app/admin/src/generated` から生成された型が正しく使用されているか

## TypeScript
- [ ] `any` 型を使用していないか。適切なインターフェース/型を使用しているか
- [ ] 厳格な null チェックが行われているか

## Astro
- [ ] レイアウトや静的コンテンツに `.astro` ファイルを適切に使用しているか
- [ ] アイランドアーキテクチャ: 必要な場合のみコンポーネントをハイドレート(`client:load`, `client:visible` など)しているか
