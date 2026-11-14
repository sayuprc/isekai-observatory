import "../../shared";
import "../tags";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

model ReleaseGroupCreateRequest {
  title: releaseGroupTitle;
  type: ReleaseGroupTypeValue;
  description: string;
  note: note;
  isDisplay: boolean;
  orderNo: orderNo;
  tags: RequestTag[];
}

model ReleaseGroupCreateResponse {
  releaseGroup: ReleaseGroup;
}

model ReleaseGroupUpdateRequest {
  title: releaseGroupTitle;
  type: ReleaseGroupTypeValue;
  description: string;
  note: note;
  isDisplay: boolean;
  orderNo: orderNo;
  tags: RequestTag[];
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
  note: note;
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
  note: note;
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

@doc("リリースグループ一括更新の指定。付与・除去するタグと表示設定は、指定したものだけを反映する")
model ReleaseGroupBulkUpdateRequest {
  @doc("対象のリリースグループID。1 リクエストで受け付ける上限は 100 件")
  @minItems(1)
  @maxItems(100)
  releaseGroupIds: releaseGroupId[];

  @doc("付与するタグ。付いているものは何もしない")
  addTagIds?: tagId[];

  @doc("除去するタグ。付いていないものは何もしない")
  removeTagIds?: tagId[];

  @doc("表示設定の切り替え先")
  isDisplay?: boolean;
}

model ReleaseGroupBulkUpdateResponse {
  @doc("更新したリリースグループの件数")
  updatedCount: int32;
}
