import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

@doc("タグID")
scalar tagId extends uuid;

@doc("タグ名")
@minLength(1)
scalar tagName extends string;

@example(#{ tagId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4", name: "テストタグA", orderNo: 10 })
model Tag {
  tagId: tagId;
  name: tagName;
  orderNo: orderNo;
}

@doc("タグ一覧の 1 行分")
model TagSummary {
  ...Tag;

  @doc("このタグが付いている楽曲の件数")
  songCount: int32;
}
