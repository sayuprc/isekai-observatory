import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Admin;

@doc("楽曲タグID")
scalar songTagId extends uuid;

@doc("楽曲タグ名")
@minLength(1)
scalar songTagName extends string;

@example(#{ songTagId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4", name: "テストタグA", orderNo: 10 })
model SongTag {
  songTagId: songTagId;
  name: songTagName;
  orderNo: orderNo;
}
