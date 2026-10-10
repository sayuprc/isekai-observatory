import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Public;

@doc("人物ID")
scalar personId extends uuid;

@doc("人物。公開楽曲のクレジット、公開イベントの共演者、公開人物グループのメンバーとして参照されているものだけが載る")
model Person {
  personId: personId;
  name: string;
}
