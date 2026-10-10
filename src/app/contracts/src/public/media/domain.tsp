import "../../packages/media";

using IsekaiObservatory.Packages.Media;

namespace IsekaiObservatory.Public;

@doc("公開メディア")
model Media {
  mediaId: mediaId;
  title: mediaTitle;
  url: mediaUrl;
  publishedAt: mediaPublishedAt;
  type: MediaTypeValue;
}
