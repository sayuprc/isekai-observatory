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

## 管理画面の画面構成

- 詳細・作成画面の構成は ADR-0030 に従い、`EntityHeader` / `ActionMenu` / `TabList` / `FormRow` / `SegmentedControl` を使う
- 枠が複数ある画面やタブは `FormColumns` で組み、広い画面では主な入力と付随する一覧を左右に並べる
- 未保存検知は、入力を状態で持つフォームは `createDirtyTracker`、`FormData` で読むフォームは `createFormDirtyTracker` を使う
- 保存・削除・破棄のあとに画面を移るときは、離脱の確認を出さないよう `allowLeave()` を呼んでから移る
- 一覧の件数列は ADR-0031 に従い、検索 API の `*Summary` 型から `CountCell` で出す

## client:load の部品で守ること

- `client:load` の部品はサーバーでも描画され、描画後に `onCleanup` も走る
- `window` / `document` / `localStorage` への登録・解除・読み取りは、`onMount` の中で行う
- JSX として受け取った props を `Show` の条件と中身のように 2 回参照しない。`children()` で 1 回だけ評価して使い回す
- 2 回参照すると、サーバーで余分な要素が作られ、ハイドレーションで要素を照合できなくなる

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
- `mise run admin:check` / `mise run viewer:check` や `cd src/app && bun --filter <package> build` を確認したか
