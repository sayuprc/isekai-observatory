import "../../shared";
import "../../packages/media";
import "../../packages/song";
import "../media";
import "../persons";
import "../song-tags";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Media;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Admin;

@doc("歌詞リンク")
@format("uri")
scalar lyricsLink extends string;

@example(#{
  songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
  title: "テスト楽曲",
  description: "テスト楽曲説明",
  lyricsLink: "https://example.com/lyrics",
  type: #{ name: "オリジナル曲", value: SongTypeValue.Original },
  isDisplay: true,
  orderNo: 1,
  persons: #[
    #{
      personId: "a4d01e9d-e593-4bd9-ba83-e9a7dd3887cd",
      name: "テスト人物",
      role: SongPersonRole.Lyricist,
      orderNo: 1,
    },
    #{ personId: "a4d01e9d-e593-4bd9-ba83-e9a7dd3887cd", name: "テスト人物", role: SongPersonRole.Composer, orderNo: 2 }
  ],
  tags: #[#{ songTagId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4", name: "テストタグA" }],
  media: #[
    #{
      mediaId: "11111111-1111-1111-1111-111111111111",
      title: "テストメディアMV",
      url: "https://example.com/media",
      publishedAt: mediaPublishedAt.fromISO("2024-03-01T12:00:00+09:00"),
      type: #{ name: "MV", value: MediaTypeValue.Mv },
      isDisplay: true,
      orderNo: 1,
    }
  ],
})
model Song {
  songId: songId;
  title: title;
  description: description;
  lyricsLink: lyricsLink | null;
  type: SongType;
  isDisplay: boolean;
  orderNo: orderNo;
  persons: SongPerson[];
  tags: SongAttachedTag[];
  media: SongLinkedMedia[];
}

@example(#{
  songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4",
  title: "テスト楽曲",
  type: #{ name: "オリジナル曲", value: SongTypeValue.Original },
  isDisplay: true,
  orderNo: 1,
  performanceCount: 3,
  mediaCount: 1,
  personCount: 2,
  releaseCount: 1,
})
model SongSummary {
  songId: songId;
  title: title;
  type: SongType;
  isDisplay: boolean;
  orderNo: orderNo;

  @doc("楽曲披露の件数")
  performanceCount: int32;

  @doc("リンクしているメディアの件数")
  mediaCount: int32;

  @doc("作詞・作曲・編曲として登録されている人物の人数。役割が重なる人物は 1 人と数える")
  personCount: int32;

  @doc("収録しているリリースの件数")
  releaseCount: int32;
}

@IsekaiObservatory.Shared.enumDescriptions
enum SongPersonRole {
  @doc("作詞")
  Lyricist: 1,

  @doc("作曲")
  Composer: 2,

  @doc("編曲")
  Arranger: 3,
}

model SongPerson {
  personId: personId;
  name: personName;
  role: SongPersonRole;
  orderNo: orderNo;
}

model SongAttachedTag {
  songTagId: songTagId;
  name: songTagName;
}

@example(#{
  mediaId: "11111111-1111-1111-1111-111111111111",
  title: "テストメディアMV",
  url: "https://example.com/media",
  publishedAt: mediaPublishedAt.fromISO("2024-03-01T12:00:00+09:00"),
  type: #{ name: "MV", value: MediaTypeValue.Mv },
  isDisplay: true,
  orderNo: 1,
})
model SongLinkedMedia is Media {
  orderNo: orderNo;
}

model RequestSongMediaLink {
  mediaId: mediaId;
  orderNo: orderNo;
}
