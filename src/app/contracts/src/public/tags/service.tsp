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
  @doc("公開した楽曲・イベント・リリースグループ・メディアに付いているタグを返します。並び順は表示順です")
  @returnsDoc("タグの一覧")
  listTags(@query pageToken?: pageToken, @query pageSize?: pageSize):
    | Ok<TagListResponse>
    | BadRequest
    | InternalServerError;
}
