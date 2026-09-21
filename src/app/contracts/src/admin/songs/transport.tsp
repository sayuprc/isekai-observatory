import "../../shared";
import "../../packages/song";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Admin;

model SongSearchResponse {
  songs: SongSummary[];
  maxPage: int32;
}

model SongGetResponse {
  song: Song;
}

model SongCreateRequest {
  title: title;
  description: description;
  lyricsLink: lyricsLink | null;
  typeValue: SongTypeValue;
  isDisplay: boolean;
  persons: RequestSongPerson[];
  tags: RequestSongTag[];
  media: RequestSongMediaLink[];
}

model SongCreateResponse {
  song: Song;
}

model SongUpdateRequest {
  title: title;
  description: description;
  lyricsLink: lyricsLink | null;
  typeValue: SongTypeValue;
  isDisplay: boolean;
  orderNo: orderNo;
  persons: RequestSongPerson[];
  tags: RequestSongTag[];
  media: RequestSongMediaLink[];
}

model SongUpdateResponse {
  song: Song;
}

model RequestSongPerson is PickProperties<SongPerson, "personId" | "role" | "orderNo">;

model RequestSongTag is PickProperties<SongTag, "songTagId">;
