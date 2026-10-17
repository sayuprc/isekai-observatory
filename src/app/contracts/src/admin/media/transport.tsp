import "../../packages/media";
import "../tags";

using IsekaiObservatory.Packages.Media;

namespace IsekaiObservatory.Admin;

model MediaSearchResponse {
  media: MediaSummary[];
  maxPage: int32;
}

model MediaGetResponse {
  media: Media;
  tags: AttachedTag[];
  songs: MediaReferencedSong[];
}

model MediaCreateRequest {
  title: mediaTitle;
  url: mediaUrl;
  publishedAt: mediaPublishedAt;
  type: MediaTypeValue;
  isDisplay: boolean;
  tags: RequestTag[];
}

model MediaCreateResponse {
  media: Media;
  tags: AttachedTag[];
}

model MediaUpdateRequest {
  title: mediaTitle;
  url: mediaUrl;
  publishedAt: mediaPublishedAt;
  type: MediaTypeValue;
  isDisplay: boolean;
  tags: RequestTag[];
}

model MediaUpdateResponse {
  media: Media;
  tags: AttachedTag[];
}
