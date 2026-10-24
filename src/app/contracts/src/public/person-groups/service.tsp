import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("PersonGroup")
@route("/person-groups")
interface PersonGroupService {
  @summary("人物グループ一覧")
  @doc("公開イベントの共演者の出演グループとして参照されている人物グループを返します。並び順は表示用名称順です。検索条件を指定した場合は、全文検索があれば関連度順、絞り込みだけなら通常の並び順です")
  @returnsDoc("人物グループの一覧")
  listPersonGroups(
    @doc("名前に含まれる語での絞り込み。空白区切りの語をすべて含むものに絞ります") @query name?: string,
    @query pageToken?: pageToken,
    @query pageSize?: pageSize,
  ): Ok<PersonGroupListResponse> | BadRequest | InternalServerError;
}
