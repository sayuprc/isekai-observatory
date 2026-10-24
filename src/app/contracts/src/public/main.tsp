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
  ヰ世界観測所の公開記録を読み取るための API です
  
  ## データの更新
  
  - サイトのデプロイ時に書き出したスナップショットを返します。管理画面での更新は次のデプロイまで反映されません
  - デプロイをまたいでページを送ると、新旧のデータが混ざって重複や取りこぼしが起きることがあります
  
  ## 参照
  
  - 要素どうしの参照は ID で返します。名前などは対応する一覧から引き当ててください
  - 参照先の楽曲が非公開のときは `songId` が null になり、曲名だけが載ります
  
  ## ページ送り
  
  - `pageSize` は 1〜100 で、省略すると 100 です
  - 続きがあるときはレスポンスに `nextPageToken` が載るので、次のリクエストの `pageToken` に渡してください
  - 並び順は一覧ごとに決まっていて、同順のものは ID 順です
  
  ## 検索
  
  - 一覧には、フィールドを指定した検索条件をクエリパラメータで渡せます。指定できるものは一覧ごとに異なります
  - 全文検索のパラメータ (`title` など) は、空白区切りの語をすべて含むものに絞ります。日本語は 2 文字以上の連なりで一致を見ます
  - 絞り込みのパラメータ (`type` など) は完全一致です。カンマ区切りで複数指定すると、いずれかに一致するものに絞ります
  - 複数のパラメータを指定すると、すべてを満たすものに絞ります
  - 全文検索の条件があるときは関連度の高い順、絞り込みだけのときは通常の並び順で返ります
  - 検索結果にも `pageSize` と `pageToken` が使えます
  
  ## 利用条件
  
  - 認証は不要です
  - アクセスが多すぎると 429 を返すことがあります
  
  ## バージョン
  
  - パスは `/v1/...` とし、互換性のない変更は `/v2` として並行して提供します
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
@@doc(BadRequest, "`pageSize` が範囲外、または `pageToken` が不正です");
@@doc(InternalServerError, "サーバー側でエラーが発生しました");

@enumExtensions
enum Version {
  @doc("バージョン 1")
  v1,
}
