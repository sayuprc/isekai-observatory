import "../../shared";
import "../../packages/media";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Media;

namespace IsekaiObservatory.Admin;

model Media {
  mediaId: mediaId;
  title: mediaTitle;
  url: mediaUrl;
  publishedAt: mediaPublishedAt;
  type: MediaType;
  isDisplay: boolean;
}

@doc("メディア一覧の 1 行分")
model MediaSummary {
  ...Media;

  @doc("このメディアをリンクしている楽曲の件数")
  songCount: int32;

  @doc("このメディアを関連づけているイベントの件数")
  eventCount: int32;
}

@doc("メディアを参照している楽曲ID")
scalar mediaReferencedSongId extends uuid;

@doc("メディアを参照している楽曲名")
@minLength(1)
scalar mediaReferencedSongTitle extends string;

model MediaReferencedSong {
  songId: mediaReferencedSongId;
  title: mediaReferencedSongTitle;
  songOrderNo: orderNo;
  mediaOrderNo: orderNo;
}
