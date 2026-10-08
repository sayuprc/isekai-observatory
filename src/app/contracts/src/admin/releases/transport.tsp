import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

model ReleaseGroupCreateRequest {
  title: releaseGroupTitle;
  type: ReleaseGroupTypeValue;
  description: string;
  isDisplay: boolean;
  orderNo: orderNo;
}

model ReleaseGroupCreateResponse {
  releaseGroup: ReleaseGroup;
}

model ReleaseGroupUpdateRequest {
  title: releaseGroupTitle;
  type: ReleaseGroupTypeValue;
  description: string;
  isDisplay: boolean;
  orderNo: orderNo;
}

model ReleaseGroupUpdateResponse {
  releaseGroup: ReleaseGroup;
}

model ReleaseGroupGetResponse {
  releaseGroup: ReleaseGroup;
  releases: ReleaseGroupReferencedRelease[];
}

model ReleaseGroupSearchResponse {
  releaseGroups: ReleaseGroupSummary[];
  maxPage: int32;
}

model ReleaseCreateRequest {
  releaseGroupId: releaseGroupId;
  name: releaseName;
  releasedOn: releasedOn;
  description: string;
  color: color;
  isDisplay: boolean;
  orderNo: orderNo;

  @minItems(1)
  formats: ReleaseFormatValue[];

  media: Medium[];
}

model ReleaseCreateResponse {
  release: Release;
}

model ReleaseUpdateRequest {
  name: releaseName;
  releasedOn: releasedOn;
  description: string;
  color: color;
  isDisplay: boolean;
  orderNo: orderNo;

  @minItems(1)
  formats: ReleaseFormatValue[];

  media: Medium[];
}

model ReleaseUpdateResponse {
  release: Release;
}

model ReleaseGetResponse {
  release: Release;

  @doc("所属リリースグループのタイトル。編集対象の文脈表示用")
  releaseGroupTitle: releaseGroupTitle;

  songs: ReleaseReferencedSong[];
}
