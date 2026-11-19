---
id: ADR-0042
status: accepted
superseded_by: null
applies_to: [api, admin, viewer]
---

# アプリケーションイメージは Docker Buildx Bake でビルドし、緊急時はベースイメージを差し替える

## Context

`infra/docker` の 7 イメージは、Cloud Build の 3 環境と GitHub Actions の QA がそれぞれ `docker build` の引数を持っていた

同じビルド定義が 4 箇所に複写され、手元で同じビルドを再現する入口も無かった

ベースイメージは Docker Hub から取得している

上流の修正を待てない脆弱性や Docker Hub 側の不具合が起きたとき、手元でベースイメージを作り直す手段が無かった

## Decision

`infra/docker` の 7 イメージは、リポジトリ直下の `docker-bake.hcl` を唯一のビルド定義にする

- Cloud Build / GitHub Actions / 手元は `docker buildx bake -f docker-bake.hcl` を呼び、差分は環境変数で渡す
- `DEPLOY_ENV` と `PUBLIC_APP_URL` は既定値を持たせず、呼び出し側で必ず渡す
- ベースイメージの版は ADR-0024 どおり Dockerfile の `FROM` / `COPY --from` に直接書き、通常は Docker Hub のイメージを使う

緊急時は、Docker Official Image のビルドコンテキストを `infra/docker/base/` にコミットし、Bake の named context で差し替える

- `tools/fetch-docker-library-image.sh` が docker-library/official-images から取得元 commit を引き、Docker Hub と同じソースを取得する
- `docker-bake.hcl` の `BASE_OVERRIDES` に「Dockerfile に書いたイメージ名 => ディレクトリ」を書くと、全ターゲットでそのイメージ名がローカルビルドに置き換わる
- Dockerfile の `FROM` は書き換えない
- 上流で修正版が出たら、`BASE_OVERRIDES` と `infra/docker/base/` 配下を削除して Docker Hub に戻す

## Consequences

### Positive

- ビルド定義が 1 箇所になり、手元と Cloud Build で同じビルドを再現できる
- 緊急時もアプリの Dockerfile を触らずにベースイメージだけを差し替えられる
- 差し替えたソースと書き換え内容がコミット履歴に残る
- registry cache を Bake が直接読むため、Cloud Build の事前 pull step が不要になる

### Negative

- Cloud Build では 7 イメージのビルドが 1 step にまとまり、どれか 1 つの失敗で全 push が止まる
- `BASE_OVERRIDES` のキーは `FROM` の文字列と完全一致が必要で、Renovate が `FROM` を更新すると差し替えが黙って外れる
- debian のように Dockerfile を持たない (oci-import) Official Image や、Official Image 以外のイメージは取得スクリプトの対象外で、ビルドコンテキストを手で用意する
