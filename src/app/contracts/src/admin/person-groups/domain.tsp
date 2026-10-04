import "../../shared";
import "../persons";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

@doc("人物グループID")
scalar personGroupId extends uuid;

@doc("人物グループ名")
@minLength(1)
scalar personGroupName extends string;

@doc("人物グループのメンバー")
model PersonGroupMember {
  personId: personId;
  name: personName;
  orderNo: orderNo;
}

@doc("共演者をまとめて入力・表示するための人物グループ")
@example(#{
  personGroupId: "6f0f4c1e-5d6a-4c55-9a43-0d7c2b1a8e10",
  name: "テストグループ",
  members: #[#{ personId: "0cc5f492-22fe-4f38-b702-8b2375bc0254", name: "テスト人物", orderNo: 1 }],
})
model PersonGroup {
  personGroupId: personGroupId;
  name: personGroupName;
  members: PersonGroupMember[];
}
