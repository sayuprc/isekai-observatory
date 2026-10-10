import "../../shared";
import "../people/domain.tsp";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Public;

@doc("人物グループID")
scalar personGroupId extends uuid;

@doc("人物グループ。公開イベントの共演者の出演グループとして参照されているものだけが載る")
model PersonGroup {
  personGroupId: personGroupId;
  name: string;

  @doc("メンバーの人物 ID (表示順)")
  memberIds: personId[];
}
