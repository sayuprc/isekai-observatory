import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("Tag")
@route("/tags")
interface TagService {
  @summary("タグ一覧")
  @doc("公開した楽曲・イベント・リリースグループ・メディアに付いているタグを返します。並び順は表示順です。検索条件を指定した場合は、全文検索があれば関連度順、絞り込みだけなら通常の並び順です")
  @returnsDoc("タグの一覧")
  listTags(
    @doc("名前に含まれる語での絞り込み。空白区切りの語をすべて含むものに絞ります") @query name?: string,
    @query pageToken?: pageToken,
    @query pageSize?: pageSize,
  ): Ok<TagListResponse> | BadRequest | InternalServerError;
}
