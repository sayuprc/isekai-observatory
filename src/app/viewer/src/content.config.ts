import { defineCollection } from 'astro:content';
import { mediaRepository } from './features/media/api';
import { mediaCollectionItemSchema } from './features/media/schema';
import { releaseGroupRepository } from './features/releases/api';
import { releaseGroupCollectionItemSchema } from './features/releases/schema';
import { songRepository } from './features/songs/api';
import { songCollectionItemSchema } from './features/songs/schema';

const songs = defineCollection({
  loader: async () => (await songRepository.all()).map((song, index) => ({
    id: song.songId,
    ...song,
    index,
  })),
  schema: songCollectionItemSchema,
});

const releaseGroups = defineCollection({
  loader: async () => (await releaseGroupRepository.all()).map((releaseGroup, index) => ({
    id: releaseGroup.releaseGroupId,
    ...releaseGroup,
    index,
  })),
  schema: releaseGroupCollectionItemSchema,
});

const media = defineCollection({
  loader: async () => (await mediaRepository.all()).map((mediaItem, index) => ({
    id: mediaItem.mediaId,
    ...mediaItem,
    index,
  })),
  schema: mediaCollectionItemSchema,
});

export const collections = {
  media,
  releaseGroups,
  songs,
};
