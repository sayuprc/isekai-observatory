import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Packages.Song;

@doc("楽曲ID")
@friendlyName("songId")
scalar songId extends uuid;

@doc("楽曲名")
@minLength(1)
@friendlyName("title")
scalar title extends string;

@doc("楽曲の説明")
@friendlyName("description")
scalar description extends string;

@doc("楽曲種別名")
@minLength(1)
@friendlyName("songTypeName")
scalar songTypeName extends string;

@doc("楽曲種別の値")
@friendlyName("SongTypeValue")
enum SongTypeValue {
  Original: 1,
  Cover: 2,
}

@example(#{ name: "オリジナル曲", value: SongTypeValue.Original })
@friendlyName("SongType")
model SongType {
  name: songTypeName;
  value: SongTypeValue;
}
