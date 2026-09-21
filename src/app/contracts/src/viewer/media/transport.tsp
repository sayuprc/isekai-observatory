import "../../shared";
import "../../packages/media";
import "../../packages/song";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Media;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Viewer;

@example(#{
  media: #[
    #{
      mediaId: "11111111-1111-1111-1111-111111111111",
      title: "テストメディアMV",
      url: "https://example.com/media/1",
      publishedAt: mediaPublishedAt.fromISO("2024-03-01T12:00:00+09:00"),
      type: #{ name: "MV", value: MediaTypeValue.Mv },
      counts: #{ songCount: 1 },
      songs: #[
        #{
          songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
          title: "テスト楽曲",
          type: #{ name: "オリジナル曲", value: SongTypeValue.Original },
        }
      ],
    }
  ],
  nextCursor: "eyJwdWJsaXNoZWRBdCI6IjIwMjQtMDMtMDEiLCJtZWRpYUlkIjoiMTExMTExMTEtMTExMS0xMTExLTExMTEtMTExMTExMTExMTExIn0=",
})
model MediaListResponse {
  media: MediaListItem[];

  @doc("続きがある場合のみ返す")
  nextCursor?: cursor;
}
