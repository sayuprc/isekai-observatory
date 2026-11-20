---
id: ADR-0042
status: accepted
superseded_by: null
applies_to: [api, admin, viewer]
---

# アプリケーションイメージは Docker Buildx Bake でビルドし、ベースイメージの取得元を切り替えられるようにする

## Context

`infra/docker` の 7 イメージは、Cloud Build の 3 環境と GitHub Actions の QA がそれぞれ `docker build` の引数を持っていた

同じビルド定義が 4 箇所に複写され、手元で同じビルドを再現する入口も無かった

ベースイメージは Docker Hub などの registry から取得している

上流の修正を待てない脆弱性や registry 側の不具合が起きたとき、手元でベースイメージを作り直す手段が無かった

緊急対応では、触るファイルを最小にして手順を単純にしたい

## Decision

`infra/docker` の 7 イメージは、リポジトリ直下の `docker-bake.hcl` を唯一のビルド定義にする

- Cloud Build / GitHub Actions / 手元は `docker buildx bake -f docker-bake.hcl` を呼び、差分は環境変数で渡す
- `DEPLOY_ENV` と `PUBLIC_APP_URL` は既定値を持たせず、呼び出し側で必ず渡す
- `infra/local/docker` のローカル開発 / server-qa 用イメージも同じファイルの `local` グループで定義し、mise タスクから呼ぶ

ベースイメージは `docker-bake.hcl` の `BASE_IMAGES` に定義し、Dockerfile からは `base-<キー>` の named context で参照する

- `FROM` / `COPY --from` / `RUN --mount from=` に registry のイメージ名を直接書かない
- 各エントリは `image` と `source` を持つ
- `source = "dockerhub"` は `image` を registry から取得する。通常はこれを使う
- `source = "src"` は `infra/docker/base/<キー>/` をビルドして使う。緊急時だけ切り替える
- 緊急時に変更するのは `docker-bake.hcl` の `source` と `infra/docker/base/` だけにする
- `tools/fetch-docker-library-image.sh` は `source = "src"` のエントリの `image` を読み、docker-library/official-images から Docker Hub と同じビルドコンテキストを取得する

infra/docker のベースイメージの版は、ADR-0024 の「Dockerfile に直接書く」ではなく `BASE_IMAGES` に書く

- Renovate は `renovate.json` の regex manager で `BASE_IMAGES` の `image` を更新する
- Dockerfile manager は `base-*` を registry のイメージとして扱わないよう無効にする

## Consequences

### Positive

- ビルド定義が 1 箇所になり、手元と Cloud Build で同じビルドを再現できる
- 緊急時は Dockerfile を触らず、`source` の切り替えとソースの追加だけでベースイメージを差し替えられる
- イメージ名が `BASE_IMAGES` にしか無いため、Renovate の更新で差し替えが外れることが無い
- `source = "src"` の間も Renovate が `image` を更新するので、上流の修正版が出たことに気づける
- 差し替えたソースと書き換え内容がコミット履歴に残る
- registry cache の読み込みと push を Bake が行うため、Cloud Build の pull / push step が不要になる

### Negative

- Dockerfile 単体の `docker build` は `base-*` を解決できず失敗し、必ず Bake を通す必要がある
- Renovate のベースイメージ更新は regex manager 頼みになり、`BASE_IMAGES` の 1 エントリ 1 行の書式を崩すと検知できない
- Cloud Build と Infra Docker QA では 7 イメージのビルドが 1 step にまとまり、どのイメージが失敗したかはログを見ないと分からない
- Cloud Build では全イメージのビルドが終わるまで、どの deploy も始まらない
- `src` のベースの Dockerfile 自体の `FROM` は `BASE_IMAGES` を通らず、registry から取得する
- debian のように Dockerfile を持たない (oci-import) Official Image や、Official Image 以外のイメージは取得スクリプトの対象外で、ビルドコンテキストを手で用意する
