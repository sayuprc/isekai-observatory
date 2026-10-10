import "../../shared";
import "../../packages/song";

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

@doc("代表色")
@pattern("^#[0-9a-f]{6}$")
scalar color extends string;

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

@doc("収録トラック。title は上書き名があればそれ、なければ楽曲名。楽曲を参照しないトラックと非公開楽曲のトラックは songId が null になる")
model ReleaseTrack {
  trackNo: orderNo;
  songId: songId | null;
  title: string;
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

  @doc("版名。無しは空文字")
  name: string;

  releasedOn: releasedOn;
  description: string;
  color: color;
  formats: ReleaseFormatValue[];
  media: ReleaseMedium[];
}

@doc("リリースグループ。公開リリースを 1 件以上持つものだけが載る")
model ReleaseGroup {
  releaseGroupId: releaseGroupId;
  title: string;
  type: ReleaseGroupTypeValue;
  description: string;

  @doc("公開リリース (表示順)")
  releases: Release[];
}
