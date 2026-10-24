import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("Venue")
@route("/venues")
interface VenueService {
  @summary("開催先一覧")
  @doc("公開イベントから参照されている開催先を返します。並び順は名前順です。検索条件を指定した場合は、全文検索があれば関連度順、絞り込みだけなら通常の並び順です")
  @returnsDoc("開催先の一覧")
  listVenues(
    @doc("名前に含まれる語での絞り込み。空白区切りの語をすべて含むものに絞ります") @query name?: string,

    @doc("種類での絞り込み。カンマ区切りで複数指定すると、いずれかに一致するものに絞ります")
    @query(#{ explode: false })
    kind?: VenueKindValue[],

    @query pageToken?: pageToken,
    @query pageSize?: pageSize,
  ): Ok<VenueListResponse> | BadRequest | InternalServerError;
}
