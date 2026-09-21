import "../../shared";
import "../../packages/song";
import "../songs";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Admin;

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

@doc("代表色")
@pattern("^#[0-9a-f]{6}$")
scalar color extends string;

@doc("トラックの表示名。参照トラックでは楽曲名の上書き、タイトルのみトラックでは必須のタイトル")
@minLength(1)
scalar trackTitle extends string;

@doc("リリースグループ種別")
enum ReleaseGroupTypeValue {
  Single: 1,
  Album: 2,
  Ep: 3,
  Other: 99,
}

@doc("提供形態")
enum ReleaseFormatValue {
  Digital: 1,
  Cd: 2,
  Dvd: 3,
  BluRay: 4,
  Other: 99,
}

@doc("媒体の表示ラベル(CD1 / Blu-ray など)")
@minLength(1)
scalar mediumName extends string;

@doc("リリースグループ(作品)")
model ReleaseGroup {
  releaseGroupId: releaseGroupId;
  title: releaseGroupTitle;
  typeValue: ReleaseGroupTypeValue;
  description: string;
  isDisplay: boolean;
  orderNo: orderNo;
}

@doc("リリースグループ一覧の 1 行分。firstReleasedOn は傘下リリースの最古発売日(リリース未登録なら null)")
model ReleaseGroupSummary {
  releaseGroupId: releaseGroupId;
  title: releaseGroupTitle;
  typeValue: ReleaseGroupTypeValue;
  description: string;
  isDisplay: boolean;
  orderNo: orderNo;
  firstReleasedOn: releasedOn | null;
}

@doc("リリースグループ傘下のリリース 1 件分の概要")
model ReleaseGroupReferencedRelease {
  releaseId: releaseId;
  name: releaseName;
  releasedOn: releasedOn;
  color: color;
  isDisplay: boolean;
  orderNo: orderNo;
  formatValues: ReleaseFormatValue[];
}

@doc("収録曲。songId / title の少なくとも一方を指定する(検証はサーバー側の責務)。songId ありは Song 集約を参照する参照トラックで、title を併記すると表示名を上書きする。songId なしは表示専用のタイトルのみトラック")
model Track {
  songId: uuid | null;
  title: trackTitle | null;
  trackNo: orderNo;
}

@doc("リリース内の媒体(トラックリストの区切り)。name は Disc 表示用の任意ラベル")
model Medium {
  position: orderNo;
  name: mediumName | null;
  tracks: Track[];
}

@doc("リリース(実際に世に出た版・盤)")
model Release {
  releaseId: releaseId;
  releaseGroupId: releaseGroupId;
  name: releaseName;
  releasedOn: releasedOn;
  description: string;
  color: color;
  isDisplay: boolean;
  orderNo: orderNo;

  @minItems(1)
  formatValues: ReleaseFormatValue[];

  media: Medium[];
}

@doc("リリース収録曲の read model。参照トラックの title は楽曲の正式名(上書き名は反映しない)、タイトルのみトラックは songId: null でトラックタイトルが載る")
model ReleaseReferencedSong {
  mediumPosition: orderNo;
  trackNo: orderNo;
  songId: uuid | null;
  title: title;
}
