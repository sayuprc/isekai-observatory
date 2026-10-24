import "@typespec/http";
import "../../shared";
import "../../packages/media";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;
using IsekaiObservatory.Packages.Media;

namespace IsekaiObservatory.Public;

@tag("Media")
@route("/media")
interface MediaService {
  @summary("メディア一覧")
  @doc("公開メディアを返します。並び順はタイトル順です。検索条件を指定した場合は、全文検索があれば関連度順、絞り込みだけなら通常の並び順です")
  @returnsDoc("メディアの一覧")
  listMedia(
    @doc("タイトルに含まれる語での絞り込み。空白区切りの語をすべて含むものに絞ります") @query title?: string,

    @doc("種別での絞り込み。カンマ区切りで複数指定すると、いずれかに一致するものに絞ります")
    @query(#{ explode: false })
    type?: MediaTypeValue[],

    @query pageToken?: pageToken,
    @query pageSize?: pageSize,
  ): Ok<MediaListResponse> | BadRequest | InternalServerError;
}
