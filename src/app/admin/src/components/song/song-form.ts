import { createSignal } from 'solid-js';
import type {
  Media,
  RequestSongPerson,
  Song,
  SongCreateRequest,
  SongPerson,
  SongTypeValue,
  SongUpdateRequest,
} from '../../generated';
import { toMediaEntry, type MediaEntry } from '../media/MediaSection';
import { buildSongMediaRequest } from './media-request';
import { toRequestSongPersons, type PersonSelections, type SelectedPerson } from './person-selection';

export type SongTagEntry = {
  songTagId: string;
};

const toSelectedPersons = (items: SongPerson[]): SelectedPerson[] =>
  items.map((item) => ({ personId: item.personId, name: item.name }));

// 楽曲がリンク済みのメディアを、選択候補の一覧にも含める
const mergeLinkedMedia = (media: Media[], song: Song | undefined): Media[] => {
  const items = [...media];
  for (const item of song?.media ?? []) {
    if (!items.some((candidate) => candidate.mediaId === item.mediaId)) {
      items.push({
        mediaId: item.mediaId,
        title: item.title,
        url: item.url,
        publishedAt: item.publishedAt,
        type: item.type,
        isDisplay: item.isDisplay,
      });
    }
  }
  return items;
};

const normalizeOptionalString = (value: string): string | null => {
  const normalized = value.trim();
  return normalized === '' ? null : normalized;
};

// 作成と編集で共通の入力状態
export const createSongForm = (options: { song?: Song; media: Media[] }) => {
  const song = options.song;
  const [title, setTitle] = createSignal(song?.title ?? '');
  const [description, setDescription] = createSignal(song?.description ?? '');
  const [lyricsLink, setLyricsLink] = createSignal(song?.lyricsLink ?? '');
  const [typeValue, setTypeValue] = createSignal<SongTypeValue | ''>(song?.type ?? '');
  const [isDisplay, setIsDisplay] = createSignal(song?.isDisplay ?? true);
  const [orderNo, setOrderNo] = createSignal(song?.orderNo ?? 1);
  const persons = song?.persons ?? [];
  const [personSelections, setPersonSelections] = createSignal<PersonSelections>({
    1: toSelectedPersons(persons.filter((person) => person.role === 1)),
    2: toSelectedPersons(persons.filter((person) => person.role === 2)),
    3: toSelectedPersons(persons.filter((person) => person.role === 3)),
  });
  const [tags, setTags] = createSignal<SongTagEntry[]>((song?.tags ?? []).map((tag) => ({ songTagId: tag.songTagId })));
  const [mediaEntries, setMediaEntries] = createSignal<MediaEntry[]>((song?.media ?? []).map(toMediaEntry));
  const [availableMedia, setAvailableMedia] = createSignal<Media[]>(mergeLinkedMedia(options.media, song));

  const buildPersons = (): RequestSongPerson[] => [
    ...toRequestSongPersons(personSelections()[1], 1),
    ...toRequestSongPersons(personSelections()[2], 2),
    ...toRequestSongPersons(personSelections()[3], 3),
  ];

  const personCount = () => new Set(buildPersons().map((person) => person.personId)).size;

  const toCreateRequest = (): SongCreateRequest => ({
    title: title(),
    description: description(),
    lyricsLink: normalizeOptionalString(lyricsLink()),
    type: typeValue() as SongTypeValue,
    isDisplay: isDisplay(),
    persons: buildPersons(),
    tags: tags(),
    media: buildSongMediaRequest(mediaEntries()),
  });

  const toUpdateRequest = (): SongUpdateRequest => ({ ...toCreateRequest(), orderNo: orderNo() });

  return {
    title,
    setTitle,
    description,
    setDescription,
    lyricsLink,
    setLyricsLink,
    typeValue,
    setTypeValue,
    isDisplay,
    setIsDisplay,
    orderNo,
    setOrderNo,
    personSelections,
    setPersonSelections,
    tags,
    setTags,
    mediaEntries,
    setMediaEntries,
    availableMedia,
    setAvailableMedia,
    personCount,
    toCreateRequest,
    toUpdateRequest,
  };
};

export type SongFormState = ReturnType<typeof createSongForm>;
