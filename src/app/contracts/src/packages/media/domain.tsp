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

@doc("メディア種別の値")
@friendlyName("MediaTypeValue")
@enumExtensions
enum MediaTypeValue {
  @doc("MV")
  Mv: 1,

  @doc("音源動画")
  AudioVideo: 2,

  @doc("配信")
  LiveStream: 3,

  @doc("ショート")
  Short: 4,

  @doc("投稿")
  Post: 5,

  @doc("その他")
  Other: 99,
}
