import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

@doc("人物ID")
scalar personId extends uuid;

@doc("人物名")
@minLength(1)
scalar personName extends string;

@example(#{ personId: "0cc5f492-22fe-4f38-b702-8b2375bc0254", name: "テスト人物", orderNo: 10 })
model Person {
  personId: personId;
  name: personName;
  orderNo: orderNo;
}
