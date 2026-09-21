# FRONTEND.md

## 目的

`src/app/admin` と `src/app/viewer` の UI 実装を、短く作れて、あとから直しやすい形で維持するための方針です

## 基本方針

- ページ責務は Astro に保ち、対話的な UI は SolidJS に分ける
- セマンティックな HTML と分かりやすい見出し構造を優先する
- 360px 幅とデスクトップ幅の両方で破綻しないことを前提にする
- 管理画面 (`src/app/admin`) は `lg` 未満で DaisyUI drawer によるサイドバー開閉、`lg` 以上で常時表示とする
- 非同期処理やフォームには loading / error / empty state を用意する
- 既存スタックで解けるなら依存を増やしすぎない
- パッケージ管理は `src/app/` の pnpm workspace で行い、依存関係は `mise run pnpm:install` でインストールする
- 各 package script は Bun 実行環境で `cd src/app && bun --filter <package> <script>` として実行する

## 境界の参照先

- `src/app/admin` / `src/app/viewer` のディレクトリ境界は `docs/design-docs/subproject-boundaries.md` を参照する

## UI 品質の最低条件

- キーボードだけでも主要操作ができる
- 色だけに依存しない UI を使う
- 主要フローで console error を出さない
- 失敗状態と空状態が画面から分かる
- 文言が仕様や画面の意図に沿っている

## 実装判断の目安

- ページ固有の軽い組み立ては `.astro` に寄せる
- 再利用や状態管理が必要な UI は `.tsx` に分ける
- 共有化は重複が見えてから行う
- API shape を変える場合は `src/app/contracts` から着手する

## 完了前の確認

- 画面幅を変えて見たか
- フォーカス移動やフォームエラー表示が破綻していないか
- loading / error / empty の分岐が見えるか
- `cd src/app && bun --filter admin lint:check` や `cd src/app && bun --filter <package> build` を確認したか
