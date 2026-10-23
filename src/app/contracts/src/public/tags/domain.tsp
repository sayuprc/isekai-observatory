import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Public;

@doc("タグID")
scalar tagId extends uuid;

@doc("タグ。公開楽曲・公開イベント・公開リリースグループ・公開メディアに付いているものだけが載ります")
model Tag {
  tagId: tagId;
  name: string;
}
