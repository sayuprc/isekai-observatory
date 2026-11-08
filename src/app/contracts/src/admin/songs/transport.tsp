import "../../shared";
import "../../packages/song";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Admin;

model SongSearchResponse {
  songs: SongSummary[];
  maxPage: int32;
}

model SongGetResponse {
  song: Song;
}

model SongCreateRequest {
  title: title;
  description: description;
  note: note;
  lyricsLink: lyricsLink | null;
  type: SongTypeValue;
  isDisplay: boolean;
  persons: RequestSongPerson[];
  tags: RequestTag[];
  media: RequestSongMediaLink[];
}

model SongCreateResponse {
  song: Song;
}

model SongUpdateRequest {
  title: title;
  description: description;
  note: note;
  lyricsLink: lyricsLink | null;
  type: SongTypeValue;
  isDisplay: boolean;
  orderNo: orderNo;
  persons: RequestSongPerson[];
  tags: RequestTag[];
  media: RequestSongMediaLink[];
}

model SongUpdateResponse {
  song: Song;
}

model RequestSongPerson is PickProperties<SongPerson, "personId" | "role" | "orderNo">;

@doc("楽曲一括更新の指定。付与・除去するタグと表示設定は、指定したものだけを反映する")
model SongBulkUpdateRequest {
  @doc("対象の楽曲ID。1 リクエストで受け付ける上限は 100 件")
  @minItems(1)
  @maxItems(100)
  songIds: songId[];

  @doc("付与するタグ。付いているものは何もしない")
  addTagIds?: tagId[];

  @doc("除去するタグ。付いていないものは何もしない")
  removeTagIds?: tagId[];

  @doc("表示設定の切り替え先")
  isDisplay?: boolean;
}

model SongBulkUpdateResponse {
  @doc("更新した楽曲の件数")
  updatedCount: int32;
}
