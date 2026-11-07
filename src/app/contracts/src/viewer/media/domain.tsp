import "../tags";
import "../../packages/media";
import "../../packages/song";

using IsekaiObservatory.Packages.Media;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Viewer;

@example(#{
  mediaId: "11111111-1111-1111-1111-111111111111",
  title: "テストメディアMV",
  url: "https://example.com/media/1",
  publishedAt: mediaPublishedAt.fromISO("2024-03-01T12:00:00+09:00"),
  type: MediaTypeValue.Mv,
  counts: #{ songCount: 1 },
  songs: #[#{ songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4", title: "テスト楽曲", type: SongTypeValue.Original }],
  tags: #[#{ tagId: "5f0c8d1e-2b7a-4c3e-9a41-6d8e2f1b7c90", name: "テストタグ", orderNo: 10 }],
})
model MediaListItem {
  mediaId: mediaId;
  title: mediaTitle;
  url: mediaUrl;
  publishedAt: mediaPublishedAt;
  type: MediaTypeValue;
  counts: MediaRelationCounts;
  songs: MediaSongSummary[];

  @doc("付いているタグ(タグの表示順)")
  tags: AttachedTag[];
}

model MediaRelationCounts {
  @doc("関連楽曲数")
  songCount: int32;
}

@example(#{ songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4", title: "テスト楽曲", type: SongTypeValue.Original })
model MediaSongSummary {
  songId: songId;
  title: title;
  type: SongTypeValue;
}
