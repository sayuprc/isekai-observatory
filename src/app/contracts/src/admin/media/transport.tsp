import "../../packages/media";
import "../tags";

using IsekaiObservatory.Packages.Media;

namespace IsekaiObservatory.Admin;

model MediaSearchResponse {
  media: MediaSummary[];
  maxPage: int32;
}

model MediaGetResponse {
  media: Media;
  tags: AttachedTag[];
  songs: MediaReferencedSong[];
}

model MediaCreateRequest {
  title: mediaTitle;
  url: mediaUrl;
  publishedAt: mediaPublishedAt;
  type: MediaTypeValue;
  isDisplay: boolean;
  tags: RequestTag[];
}

model MediaCreateResponse {
  media: Media;
  tags: AttachedTag[];
}

model MediaUpdateRequest {
  title: mediaTitle;
  url: mediaUrl;
  publishedAt: mediaPublishedAt;
  type: MediaTypeValue;
  isDisplay: boolean;
  tags: RequestTag[];
}

model MediaUpdateResponse {
  media: Media;
  tags: AttachedTag[];
}

@doc("メディア一括更新の指定。付与・除去するタグと表示設定は、指定したものだけを反映する")
model MediaBulkUpdateRequest {
  @doc("対象のメディアID。1 リクエストで受け付ける上限は 100 件")
  @minItems(1)
  @maxItems(100)
  mediaIds: mediaId[];

  @doc("付与するタグ。付いているものは何もしない")
  addTagIds?: tagId[];

  @doc("除去するタグ。付いていないものは何もしない")
  removeTagIds?: tagId[];

  @doc("表示設定の切り替え先")
  isDisplay?: boolean;
}

model MediaBulkUpdateResponse {
  @doc("更新したメディアの件数")
  updatedCount: int32;
}
