import { defineCollection } from 'astro:content';
import { eventRepository } from './features/events/api';
import type { Event } from './features/events/types';
import { mediaRepository } from './features/media/api';
import type { Media } from './features/media/types';
import { releaseGroupRepository } from './features/releases/api';
import type { ReleaseGroup } from './features/releases/types';
import { songRepository } from './features/songs/api';
import type { Song } from './features/songs/types';
import { indexedLoader, indexedSchema } from './shared/content/indexed';

// 各一覧 API は build 中に 1 回だけ全件取得し、ページ間ではコレクションを共有する
const songs = defineCollection({
  loader: indexedLoader(songRepository.all, song => song.songId),
  schema: indexedSchema<Song>(),
});

const releaseGroups = defineCollection({
  loader: indexedLoader(releaseGroupRepository.all, releaseGroup => releaseGroup.releaseGroupId),
  schema: indexedSchema<ReleaseGroup>(),
});

const media = defineCollection({
  loader: indexedLoader(mediaRepository.all, mediaItem => mediaItem.mediaId),
  schema: indexedSchema<Media>(),
});

const events = defineCollection({
  loader: indexedLoader(eventRepository.all, event => event.eventId),
  schema: indexedSchema<Event>(),
});

export const collections = {
  events,
  media,
  releaseGroups,
  songs,
};
