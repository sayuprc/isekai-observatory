import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Viewer;

@doc("タグID")
scalar tagId extends uuid;

@doc("タグ名")
@minLength(1)
scalar tagName extends string;

@doc("記録に付いたタグ")
model AttachedTag {
  tagId: tagId;
  name: tagName;

  @doc("タグの表示順。一覧のタグの選択肢を並べるのに使う")
  orderNo: orderNo;
}
