import "../../shared";
import "../../packages/song";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Viewer;

@doc("リリースグループID")
scalar releaseGroupId extends uuid;

@doc("リリースグループタイトル")
@minLength(1)
scalar releaseGroupTitle extends string;

@doc("リリースID")
scalar releaseId extends uuid;

@doc("リリース版名。任意項目で、無しは空文字")
scalar releaseName extends string;

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

@doc("収録曲が参照する楽曲。リンク可否は isDisplay で判定する")
model ReleaseTrackSong {
  songId: songId;
  title: string;
  isDisplay: boolean;
}

@doc("収録曲。songs は収録順で、2 件以上ならメドレー。title は楽曲 1 件なら上書き名、メドレーなら表示名、楽曲なしならタイトルで、無ければ null")
model ReleaseTrackItem {
  trackNo: orderNo;
  title: string | null;
  songs: ReleaseTrackSong[];
}

@doc("リリース内の媒体(トラックリストの区切り)。name は Disc 表示用の任意ラベル")
model ReleaseMediumItem {
  position: orderNo;
  name: string | null;
  tracks: ReleaseTrackItem[];
}

@doc("リリース(版)")
model ReleaseListItem {
  releaseId: releaseId;
  name: releaseName;
  releasedOn: releasedOn;
  description: string;
  orderNo: orderNo;
  formats: ReleaseFormatValue[];
  media: ReleaseMediumItem[];
}

@doc("リリースグループ。公開リリースを 1 件以上持つものだけが一覧に載る")
model ReleaseGroupListItem {
  releaseGroupId: releaseGroupId;
  title: releaseGroupTitle;
  type: ReleaseGroupTypeValue;
  description: string;

  @doc("傘下の公開リリースの最古発売日")
  firstReleasedOn: releasedOn;

  releases: ReleaseListItem[];
}
