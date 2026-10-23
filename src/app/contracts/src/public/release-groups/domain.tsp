import "../../shared";
import "../../packages/song";
import "../tags/domain.tsp";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Public;

@doc("リリースグループID")
scalar releaseGroupId extends uuid;

@doc("リリースID")
scalar releaseId extends uuid;

@doc("発売日")
@format("date")
scalar releasedOn extends string;

@doc("リリースグループ種別の値")
@enumExtensions
enum ReleaseGroupTypeValue {
  @doc("シングル")
  Single: 1,

  @doc("アルバム")
  Album: 2,

  @doc("EP")
  Ep: 3,

  @doc("その他")
  Other: 99,
}

@doc("提供形態の値")
@enumExtensions
enum ReleaseFormatValue {
  @doc("配信")
  Digital: 1,

  @doc("CD")
  Cd: 2,

  @doc("DVD")
  Dvd: 3,

  @doc("Blu-ray")
  BluRay: 4,

  @doc("その他")
  Other: 99,
}

@doc("収録トラックが参照する楽曲。参照先の楽曲が非公開なら songId は null になり、title だけが載ります")
model ReleaseTrackSong {
  songId: songId | null;
  title: title;
}

@doc("収録トラック。title は楽曲 1 件なら上書き名、メドレーなら表示名、楽曲なしならタイトルで、無ければ null です")
model ReleaseTrack {
  trackNo: orderNo;
  title: string | null;

  @doc("参照する楽曲 (収録順)。2 件以上ならメドレーです")
  songs: ReleaseTrackSong[];
}

@doc("リリース内の区切り (Disc など)")
model ReleaseMedium {
  position: orderNo;
  name: string | null;
  tracks: ReleaseTrack[];
}

@doc("公開リリース (版)")
model Release {
  releaseId: releaseId;

  @doc("版名。無しは空文字です")
  name: string;

  releasedOn: releasedOn;
  description: string;
  formats: ReleaseFormatValue[];
  media: ReleaseMedium[];
}

@doc("リリースグループ。公開リリースを 1 件以上持つものだけが載ります")
model ReleaseGroup {
  releaseGroupId: releaseGroupId;
  title: string;
  type: ReleaseGroupTypeValue;
  description: string;

  @doc("公開リリース (表示順)")
  releases: Release[];

  @doc("タグの ID (タグの表示順)")
  tagIds: tagId[];
}
