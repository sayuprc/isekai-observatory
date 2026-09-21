import "../../packages/media";

using IsekaiObservatory.Packages.Media;

namespace IsekaiObservatory.Admin;

model MediaSearchResponse {
  media: Media[];
  maxPage: int32;
}

model MediaGetResponse {
  media: Media;
  songs: MediaReferencedSong[];
}

model MediaCreateRequest {
  title: mediaTitle;
  url: mediaUrl;
  publishedAt: mediaPublishedAt;
  typeValue: MediaTypeValue;
  isDisplay: boolean;
}

model MediaCreateResponse {
  media: Media;
}

model MediaUpdateRequest {
  title: mediaTitle;
  url: mediaUrl;
  publishedAt: mediaPublishedAt;
  typeValue: MediaTypeValue;
  isDisplay: boolean;
}

model MediaUpdateResponse {
  media: Media;
}
