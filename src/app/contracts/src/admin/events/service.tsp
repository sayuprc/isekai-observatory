import "@typespec/http";
import "../auth";
import "../../packages/event";
import "../../shared";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Event;
using Http;

namespace IsekaiObservatory.Admin;

@doc("活動検索のソート条件")
enum EventSearchSortBy {
  schedule: "schedule",
  title: "title",
}

@tag("Event")
@route("/events")
@useAuth(BearerAuth)
interface EventService {
  @doc("活動検索API")
  @route("/search")
  searchEvents(
    @query title?: string,
    @query type?: EventTypeValue,
    @query status?: EventStatusValue,
    @query("is_display") isDisplay?: boolean,
    @query sort?: EventSearchSortBy = EventSearchSortBy.schedule,
    @query order?: SortOrder = SortOrder.asc,
    @query page?: page = 1,
    @query("per_page") perPage?: PerPage = PerPage.twentyFive,
  ): Ok<EventSearchResponse> | Unauthorized | Forbidden | ServerError;

  @doc("活動取得API")
  getEvent(@path eventId: uuid):
    | Ok<EventGetResponse>
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("活動作成API")
  createEvent(@body request: EventCreateRequest):
    | Ok<EventCreateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | Unprocessable
    | ServerError;

  @doc("活動更新API")
  @put
  updateEvent(@path eventId: uuid, @body request: EventUpdateRequest):
    | Ok<EventUpdateResponse>
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;

  @doc("活動削除API")
  @delete
  deleteEvent(@path eventId: uuid):
    | NoContent
    | BadRequest
    | Unauthorized
    | Forbidden
    | NotFound
    | Unprocessable
    | ServerError;
}
