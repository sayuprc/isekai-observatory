import "../../shared";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Packages.Media;

@doc("メディアID")
@friendlyName("mediaId")
scalar mediaId extends uuid;

@doc("メディアタイトル")
@minLength(1)
@friendlyName("mediaTitle")
scalar mediaTitle extends string;

@doc("メディアURL")
@format("uri")
@friendlyName("mediaUrl")
scalar mediaUrl extends string;

@doc("公開日時")
@friendlyName("mediaPublishedAt")
scalar mediaPublishedAt extends utcDateTime;

@doc("メディア種別名")
@minLength(1)
@friendlyName("mediaTypeName")
scalar mediaTypeName extends string;

@doc("メディア種別の値")
@friendlyName("MediaTypeValue")
enum MediaTypeValue {
  Mv: 1,
  AudioVideo: 2,
  LiveStream: 3,
  Short: 4,
  Post: 5,
  Other: 99,
}

@example(#{ name: "MV", value: MediaTypeValue.Mv })
@friendlyName("MediaType")
model MediaType {
  name: mediaTypeName;
  value: MediaTypeValue;
}
