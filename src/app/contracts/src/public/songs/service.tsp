import "@typespec/http";
import "../../shared";
import "../../packages/song";
import "../pagination.tsp";

using IsekaiObservatory.Shared;
using Http;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Public;

@tag("Song")
@route("/songs")
interface SongService {
  @summary("楽曲一覧")
  @doc("公開楽曲を返します。並び順は表示順の降順です。検索条件を指定した場合は、全文検索があれば関連度順、絞り込みだけなら通常の並び順です")
  @returnsDoc("楽曲の一覧")
  listSongs(
    @doc("タイトルに含まれる語での絞り込み。空白区切りの語をすべて含むものに絞ります") @query title?: string,

    @doc("種別での絞り込み。カンマ区切りで複数指定すると、いずれかに一致するものに絞ります")
    @query(#{ explode: false })
    type?: SongTypeValue[],

    @query pageToken?: pageToken,
    @query pageSize?: pageSize,
  ): Ok<SongListResponse> | BadRequest | InternalServerError;
}
