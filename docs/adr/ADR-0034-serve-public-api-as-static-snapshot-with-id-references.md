---
id: ADR-0034
status: accepted
superseded_by: null
applies_to: [api, public-api]
---

# Public API は deploy 時の静的スナップショットとして配信し、参照を ID で返す

## Context

Admin / Viewer とは別に、第三者と自分の別プロダクトが使う外部向けの API (Public API) を提供したい

インフラ費用を抑えるため、アクセスごとに API サーバーや DB へ問い合わせることは避けたい

データは現在数 MB だが、記録が増え続けるため GB 単位になっても 1 リクエストあたりのコストが増えない構造にしたい

検討した配信方法は次の 3 つ

- 静的 JSON を分割して Cloudflare に置く
- deploy 時に Cloudflare D1 へ投入し、Worker で SQL 検索を返す
- R2 に分割インデックスを置き、Worker が必要な分だけ読む

D1 は部分一致検索を書きやすいが、`LIKE '%x%'` は全件を走査し、読み取り行数の従量課金が件数に比例して増える

R2 の分割インデックスは安いが、検索エンジン相当を自作することになる

Viewer API (ADR-0021) は一覧 item に表示用の内容を埋め込むが、外部の利用者には要素ごとに正規化された形のほうが扱いやすい

## Decision

- Public API は `viewer-deploy` 時に作る静的スナップショットとして、Viewer とは別の Worker とサブドメインで配信する
  - Worker は `pageToken` を静的ファイルへ変換して切り出すだけにし、API サーバーや DB へ問い合わせない
  - 静的ファイルは 100 件単位に分割し、Worker が最大 2 ファイルを読んで `pageSize` 分を返す
- 最初は一覧だけを提供し、検索は需要が見えてから D1 などを使う別の口として追加する
- 公開する要素は Event / Song / ReleaseGroup / Media / Person / PersonGroup / Venue とする
  - Release (版) は ReleaseGroup の中に入れ子で返す
  - 要素同士の参照は ID で返す
  - 楽曲披露は `songTitle` を常に返し、参照先の Song が非公開なら `songId` を null にする。収録トラックも同様に非公開 Song の `songId` を null にする
  - Person / PersonGroup / Venue は公開記録から参照されているものだけを返す。公開した PersonGroup のメンバーも参照されているものとみなす
  - 公開境界は Viewer と同じとし、Viewer に出さないものは出さない
- 一覧の契約は次のとおり
  - パスは `/v1/...` とし、破壊的変更は `/v2` として並行提供する
  - 並び順は表示順 (`order_no`) を持つ要素はそれに、持たない要素は名前に従い、同順は ID で決める
  - `pageSize` は 1〜100 で、省略時は 100 とする
  - ページ送りは `pageToken` / `nextPageToken` で行い、token の中身は契約で保証しない
  - 不正な token、範囲外のページ、範囲外の `pageSize` には 400 を返す
  - 最終ページでは `nextPageToken` を省略する
- 認証は持たず、乱用は Cloudflare のレート制限で防ぐ。CORS は全オリジンを許可する
- スナップショットは server が Public 契約の一覧 (seek cursor) を返し、build 時に全ページをたどって静的ファイルへ書き出す
- 契約は `src/app/contracts/src/public/`、Worker と書き出し処理は `src/app/public-api/` に置く

## Consequences

### Positive

- アクセス数が増えても API サーバーと DB の負荷・費用は増えない
- データ量が増えてもファイル数が増えるだけで、1 リクエストの読み取り量は変わらない
- `nextPageToken` を不透明にしたため、配信方法を D1 などへ移しても一覧の契約を壊さない

### Negative

- 管理画面での更新は `viewer-deploy` まで反映されない
- deploy をまたいでページ送りすると、新旧のスナップショットが混ざる。記録の追加・並べ替え・非公開化・削除で重複や取りこぼしが起きうる
- 検索がない間は、利用者が全ページを取得して手元で絞り込む必要がある
- 利用者は表示に必要な内容を ID から自分で引き当てる必要がある
- Free プランではファイル数 (1 バージョン 20,000) と Worker 実行回数に上限があり、超えた日は 429 を返す
- 同じ公開データを Viewer API と Public API で二重に実装することになる
