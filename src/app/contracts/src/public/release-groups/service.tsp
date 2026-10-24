import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("ReleaseGroup")
@route("/release-groups")
interface ReleaseGroupService {
  @summary("リリースグループ一覧")
  @doc("公開リリースを 1 件以上持つリリースグループを返します。リリース (版) は `releases` に入れ子で返します。並び順は表示順の降順です。検索条件を指定した場合は、全文検索があれば関連度順、絞り込みだけなら通常の並び順です")
  @returnsDoc("リリースグループの一覧")
  listReleaseGroups(
    @doc("タイトルに含まれる語での絞り込み。空白区切りの語をすべて含むものに絞ります") @query title?: string,
    @doc("収録楽曲名に含まれる語での絞り込み。空白区切りの語をすべて含むものに絞ります") @query songTitle?: string,

    @doc("種別での絞り込み。カンマ区切りで複数指定すると、いずれかに一致するものに絞ります")
    @query(#{ explode: false })
    type?: ReleaseGroupTypeValue[],

    @query pageToken?: pageToken,
    @query pageSize?: pageSize,
  ): Ok<ReleaseGroupListResponse> | BadRequest | InternalServerError;
}
