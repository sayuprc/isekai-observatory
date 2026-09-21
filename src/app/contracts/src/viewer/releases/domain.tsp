import "../../shared";

using IsekaiObservatory.Shared;

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

@doc("代表色")
@pattern("^#[0-9a-f]{6}$")
scalar color extends string;

@doc("リリースグループ種別の値")
enum ReleaseGroupTypeValue {
  Single: 1,
  Album: 2,
  Ep: 3,
  Other: 99,
}

@example(#{ name: "アルバム", value: ReleaseGroupTypeValue.Album })
model ReleaseGroupType {
  name: string;
  value: ReleaseGroupTypeValue;
}

@doc("提供形態の値")
enum ReleaseFormatValue {
  Digital: 1,
  Cd: 2,
  Dvd: 3,
  BluRay: 4,
  Other: 99,
}

@example(#{ name: "CD", value: ReleaseFormatValue.Cd })
model ReleaseFormat {
  name: string;
  value: ReleaseFormatValue;
}

@doc("収録曲。title は上書き名があればそれ、なければ楽曲名。タイトルのみトラックは songId: null / isDisplay: false で返る。リンク可否は songId != null && isDisplay で判定する")
model ReleaseTrackItem {
  trackNo: orderNo;
  songId: uuid | null;
  title: string;
  isDisplay: boolean;
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
  color: color;
  orderNo: orderNo;
  formats: ReleaseFormat[];
  media: ReleaseMediumItem[];
}

@doc("リリースグループ。公開リリースを 1 件以上持つものだけが一覧に載る")
model ReleaseGroupListItem {
  releaseGroupId: releaseGroupId;
  title: releaseGroupTitle;
  type: ReleaseGroupType;
  description: string;

  @doc("傘下の公開リリースの最古発売日")
  firstReleasedOn: releasedOn;

  releases: ReleaseListItem[];
}
