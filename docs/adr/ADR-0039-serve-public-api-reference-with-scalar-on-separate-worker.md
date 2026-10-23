---
id: ADR-0039
status: accepted
superseded_by: null
applies_to: [public-api]
---

# Public API のリファレンスは Scalar で作り、専用の Worker とサブドメインで配信する

## Context

Public API (ADR-0038) を外部の利用者に使ってもらうには、OAS を読める形のリファレンスとして配る必要がある

Public 契約の `@server` はスナップショットの元になるローカルの Laravel (`/public/v1`) を指していて、利用者が叩く Worker の URL ではない

表示ツールは Swagger UI / Redoc / Scalar をローカルで比べ、試し打ちができて読みやすい Scalar を選んだ

## Decision

- リファレンスは Scalar で表示し、Public API の Worker とは別の、静的ファイルだけを返す Worker とサブドメインで配信する
- `viewer-deploy` で Public API のスナップショットと同じ流れで書き出してデプロイする
  - 生成済みの `IsekaiObservatory.Public.v1.yaml` をそのまま配る
  - Scalar は npm の依存に入れ、standalone ビルドを同梱する。実行時に CDN を読まない
  - Scalar の `servers` に `PUBLIC_API_URL` の `/v1` を渡し、契約の `@server` を上書きする
  - Scalar の `telemetry` は切る
- 利用者向けの説明 (更新のタイミング、ID 参照、ページ送り、利用条件) は契約の `.tsp` に書き、リファレンスはそれを表示するだけにする
  - 共通のレスポンス型は Admin / Viewer と共有しているため、Public の説明は Public の契約の中で `@@doc` で上書きする

## Consequences

### Positive

- API の Worker に手を入れず、API とリファレンスを別々に変えられる
- 環境ごとに `PUBLIC_API_URL` を変えるだけで、各環境のリファレンスがその環境の API を指す
- 契約を直せば、次のデプロイでリファレンスも追従する

### Negative

- Viewer deploy Job に Worker 名と `PUBLIC_API_URL` の 2 つの環境変数が増え、Custom Domain も 1 つ増える
- Scalar の standalone ビルド (約 4.5MB) を Worker の静的ファイルとして毎回載せる
- 生成された OAS の `servers` はローカルの Laravel のままなので、OAS を直接読む人には本番の URL が分からない
