import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("Person")
@route("/people")
interface PersonService {
  @summary("人物一覧")
  @doc("公開楽曲のクレジット、公開イベントの共演者、公開人物グループのメンバーとして参照されている人物を返します。並び順は表示順です。検索条件を指定した場合は、全文検索があれば関連度順、絞り込みだけなら通常の並び順です")
  @returnsDoc("人物の一覧")
  listPeople(
    @doc("名前に含まれる語での絞り込み。空白区切りの語をすべて含むものに絞ります") @query name?: string,
    @query pageToken?: pageToken,
    @query pageSize?: pageSize,
  ): Ok<PersonListResponse> | BadRequest | InternalServerError;
}
