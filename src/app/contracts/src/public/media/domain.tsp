import "../../packages/media";
import "../tags/domain.tsp";

using IsekaiObservatory.Packages.Media;

namespace IsekaiObservatory.Public;

@doc("公開メディア")
model Media {
  mediaId: mediaId;
  title: mediaTitle;
  url: mediaUrl;
  publishedAt: mediaPublishedAt;
  type: MediaTypeValue;

  @doc("タグの ID (タグの表示順)")
  tagIds: tagId[];
}
