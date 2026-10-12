import "../../shared";
import "../persons";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

@doc("人物グループID")
scalar personGroupId extends uuid;

@doc("人物グループの管理用名称。一意で、管理画面でグループを見分けるために使う")
@minLength(1)
scalar personGroupName extends string;

@doc("人物グループの表示用名称。閲覧サイトに出す名前で、重複してよい")
@minLength(1)
scalar personGroupDisplayName extends string;

@doc("人物グループのメンバー")
model PersonGroupMember {
  personId: personId;
  name: personName;
  orderNo: orderNo;
}

@doc("共演者をまとめて入力・表示するための人物グループ")
@example(#{
  personGroupId: "6f0f4c1e-5d6a-4c55-9a43-0d7c2b1a8e10",
  name: "テストグループ (B不在)",
  displayName: "テストグループ",
  members: #[#{ personId: "0cc5f492-22fe-4f38-b702-8b2375bc0254", name: "テスト人物", orderNo: 1 }],
})
model PersonGroup {
  personGroupId: personGroupId;
  name: personGroupName;
  displayName: personGroupDisplayName;
  members: PersonGroupMember[];
}

@doc("人物グループ一覧の 1 行分")
model PersonGroupSummary {
  ...PersonGroup;

  @doc("このグループとして共演が記録されている楽曲披露の件数")
  performanceCount: int32;
}
