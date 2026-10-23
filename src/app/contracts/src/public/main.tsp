import "@typespec/http";
import "@typespec/versioning";
import "../shared";
import "./pagination.tsp";
import "./events";
import "./media";
import "./people";
import "./person-groups";
import "./release-groups";
import "./songs";
import "./tags";
import "./venues";

using Http;
using IsekaiObservatory.Shared;
using Versioning;

@service(#{ title: "ヰ世界観測所 API" })
@doc("""
  ヰ世界観測所の公開記録を読み取るための API
  
  ## データの更新
  
  - サイトのデプロイ時に書き出したスナップショットを返す。管理画面での更新は次のデプロイまで反映されない
  - デプロイをまたいでページを送ると、新旧のデータが混ざって重複や取りこぼしが起きることがある
  
  ## 参照
  
  - 要素どうしの参照は ID で返す。名前などは対応する一覧から引き当てる
  - 参照先の楽曲が非公開のときは `songId` が null になり、曲名だけが載る
  
  ## ページ送り
  
  - `pageSize` は 1〜100 で、省略すると 100
  - 続きがあるときはレスポンスに `nextPageToken` が載るので、次のリクエストの `pageToken` に渡す
  - `pageToken` の中身の形式は保証しない
  - 並び順は一覧ごとに決まっていて、同順のものは ID 順
  
  ## 利用条件
  
  - 認証は不要で、すべてのオリジンからのリクエストを許可する (CORS)
  - アクセスが多すぎると 429 を返すことがある
  
  ## バージョン
  
  - パスは `/v1/...` とし、互換性のない変更は `/v2` として並行して提供する
  """)
@server(
  "https://local.api.isekaijoucho.fan/public/{version}",
  "ローカル環境",
  {
    version: Version,
  }
)
@versioned(Version)
namespace IsekaiObservatory.Public;

// 共通のレスポンス型は Admin / Viewer と共有しているため、説明は Public の契約の中でだけ上書きする
@@doc(BadRequest, "`pageSize` が範囲外、または `pageToken` が不正");
@@doc(InternalServerError, "サーバー側でエラーが発生した");

@enumExtensions
enum Version {
  @doc("バージョン 1")
  v1,
}
