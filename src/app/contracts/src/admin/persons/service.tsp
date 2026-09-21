import "@typespec/http";
import "../auth";
import "../../shared";

using IsekaiObservatory.Shared;
using Http;

namespace IsekaiObservatory.Admin;

@doc("人物検索のソート条件")
enum PersonSearchSortBy {
  name: "name",
  orderNo: "order_no",
}

@tag("Person")
@route("/persons")
@useAuth(BearerAuth)
interface PersonService {
  @doc("人物一覧取得API")
  listPersons(): Ok<PersonListResponse> | Unauthorized | Forbidden | ServerError;

  @doc("人物検索API")
  @route("/search")
  searchPersons(
    @query name?: string,
    @query sort?: PersonSearchSortBy = PersonSearchSortBy.orderNo,
    @query order?: SortOrder = SortOrder.asc,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.fifty,
  ): Ok<PersonSearchResponse> | Unauthorized | Forbidden | ServerError;

  @doc("人物取得API")
  getPerson(@path personId: uuid):
    | Ok<PersonGetResponse>
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("人物作成API")
  createPerson(@body request: PersonCreateRequest):
    | Ok<PersonCreateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;

  @doc("人物更新API")
  @put
  updatePerson(@path personId: uuid, @body request: PersonUpdateRequest):
    | Ok<PersonUpdateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("人物削除API")
  @delete
  deletePerson(@path personId: uuid): NoContent | BadRequest | Unauthorized | Forbidden | Unprocessable | ServerError;
}
