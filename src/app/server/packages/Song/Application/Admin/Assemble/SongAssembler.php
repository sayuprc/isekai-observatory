<?php

declare(strict_types=1);

namespace Song\Application\Admin\Assemble;

use Media\Domain\Models\Media;
use Media\Domain\Models\MediaRepositoryInterface;
use Person\Domain\Models\PersonRepositoryInterface;
use Song\Domain\Models\Media\SongMediaLink;
use Song\Domain\Models\Persons\SongPerson;
use Song\Domain\Models\Song;

class SongAssembler
{
    public function __construct(
        private readonly PersonRepositoryInterface $personRepository,
        private readonly MediaRepositoryInterface $mediaRepository,
    ) {
    }

    public function assemble(Song $song): AssembledSong
    {
        $allPersonIds = [];
        foreach ($song->persons as $person) {
            $allPersonIds[$person->personId->value] = $person->personId;
        }

        $personMap = [];
        if (! empty($allPersonIds)) {
            $persons = $this->personRepository->findByIds(...array_values($allPersonIds));
            foreach ($persons as $person) {
                $personMap[$person->personId->value] = $person;
            }
        }

        $allMediaIds = [];
        foreach ($song->media as $media) {
            $allMediaIds[$media->mediaId->value] = $media->mediaId;
        }

        $mediaMap = [];
        if (! empty($allMediaIds)) {
            $mediaItems = $this->mediaRepository->findByIds(...array_values($allMediaIds));
            foreach ($mediaItems as $media) {
                $mediaMap[$media->mediaId->value] = $media;
            }
        }

        $toAssembled = static function (SongPerson $person) use ($personMap): AssembledPerson {
            $found = $personMap[$person->personId->value] ?? null;
            // Song Entity が成立している時点で $found が null になることはない
            assert($found !== null);

            return new AssembledPerson(
                $person->personId->value,
                $found->name->value,
                $person->role,
                $person->orderNo->value,
            );
        };
        $toAssembledMedia = static function (SongMediaLink $link) use ($mediaMap): AssembledMedia {
            $found = $mediaMap[$link->mediaId->value] ?? null;
            assert($found instanceof Media);

            return new AssembledMedia(
                $found->mediaId->value,
                $found->title->value,
                $found->url->value,
                $found->publishedAt->value->format('Y-m-d H:i:s'),
                $found->type->value,
                $found->isDisplay,
                $link->orderNo->value,
            );
        };

        return new AssembledSong(
            $song->songId->value,
            $song->title->value,
            $song->description->value,
            $song->note->value,
            $song->lyricsLink?->value,
            $song->type->value,
            $song->isDisplay,
            $song->orderNo->value,
            $song->persons->toGeneric()->map($toAssembled)->toArray(),
            $song->media->toGeneric()->map($toAssembledMedia)->toArray(),
        );
    }
}
