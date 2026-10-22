import "@typespec/http";
import "../../shared";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Public;

@tag("Person")
@route("/people")
interface PersonService {
  @doc("人物一覧取得API")
  listPeople(@query pageToken?: pageToken, @query pageSize?: pageSize):
    | Ok<PersonListResponse>
    | BadRequest
    | InternalServerError;
}
