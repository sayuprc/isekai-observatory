import "@typespec/http";
import "../../shared";
import "../../packages/media";
import "../../packages/song";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Media;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Viewer;

@example(#{
  songs: #[
    #{
      songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
      title: "テスト楽曲",
      description: "テスト楽曲説明",
      type: #{ name: "オリジナル曲", value: SongTypeValue.Original },
      counts: #{ releaseCount: 1, mediaCount: 1 },
      lyricists: #["テスト作詞者"],
      composers: #["テスト作曲者"],
      arrangers: #["テスト編曲者"],
      media: #[
        #{
          mediaId: "11111111-1111-1111-1111-111111111111",
          title: "テストメディアMV",
          type: #{ name: "MV", value: MediaTypeValue.Mv },
          url: "https://www.youtube.com/watch?v=dummy",
          publishedAt: mediaPublishedAt.fromISO("2024-03-01T12:00:00+09:00"),
        }
      ],
      releaseGroups: #[
        #{
          releaseGroupId: "0f2185cd-b4ab-4f22-b0a3-fd93cbbd2df2",
          title: "テストアルバム",
          type: #{ name: "アルバム", value: ReleaseGroupTypeValue.Album },
          firstReleasedOn: "2026-05-01",
          color: "#4a5a78",
        }
      ],
    }
  ],
  nextCursor: "eyJvcmRlck5vIjoxLCJzb25nSWQiOiIzY2Q0MmMwOS1mZjNjLTRjZDItOTEzZi1hMjc5YzRlYTg5YjQifQ==",
})
model SongListResponse {
  songs: SongListItem[];

  @doc("続きがある場合のみ返す")
  nextCursor?: cursor;
}
