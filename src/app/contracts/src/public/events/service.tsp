import "@typespec/http";
import "../../shared";
import "../../packages/event";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;
using IsekaiObservatory.Packages.Event;

namespace IsekaiObservatory.Public;

@tag("Event")
@route("/events")
interface EventService {
  @summary("イベント一覧")
  @doc("公開イベントを返します。並び順はタイトル順です。検索条件を指定した場合は、全文検索があれば関連度順、絞り込みだけなら通常の並び順です")
  @returnsDoc("イベントの一覧")
  listEvents(
    @doc("タイトルに含まれる語での絞り込み。空白区切りの語をすべて含むものに絞ります") @query title?: string,
    @doc("披露した楽曲名に含まれる語での絞り込み。空白区切りの語をすべて含むものに絞ります") @query songTitle?: string,

    @doc("種別での絞り込み。カンマ区切りで複数指定すると、いずれかに一致するものに絞ります")
    @query(#{ explode: false })
    type?: EventTypeValue[],

    @doc("開催状況での絞り込み。カンマ区切りで複数指定すると、いずれかに一致するものに絞ります")
    @query(#{ explode: false })
    status?: EventStatusValue[],

    @query pageToken?: pageToken,
    @query pageSize?: pageSize,
  ): Ok<EventListResponse> | BadRequest | InternalServerError;
}
