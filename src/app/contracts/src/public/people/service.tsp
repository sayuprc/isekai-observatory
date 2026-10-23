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
  @doc("公開楽曲のクレジット、公開イベントの共演者、公開人物グループのメンバーとして参照されている人物を返します。並び順は表示順です")
  @returnsDoc("人物の一覧")
  listPeople(@query pageToken?: pageToken, @query pageSize?: pageSize):
    | Ok<PersonListResponse>
    | BadRequest
    | InternalServerError;
}
