<?php

declare(strict_types=1);

namespace Event\Application\Admin\Assemble;

use Event\Domain\Models\Event;
use Event\Domain\Models\Media\EventMediaLink;
use Event\Domain\Models\Performances\CoVocalist;
use Event\Domain\Models\Performances\PerformanceId;
use Event\Domain\Models\Performances\SongPerformance;
use Event\Domain\Models\Releases\EventReleaseLink;
use Event\Domain\Models\Setlist\SetlistItem;
use Event\Domain\Models\Sources\EventSource;
use Event\Domain\Models\Venues\EventVenueLink;
use Media\Domain\Models\Media;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaRepositoryInterface;
use Person\Domain\Models\Person;
use Person\Domain\Models\PersonGroup;
use Person\Domain\Models\PersonGroupId;
use Person\Domain\Models\PersonGroupRepositoryInterface;
use Person\Domain\Models\PersonRepositoryInterface;
use Release\Domain\Models\Release;
use Release\Domain\Models\ReleaseGroup;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Release\Domain\Models\ReleaseId;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongRepositoryInterface;
use Venue\Domain\Models\Venue;
use Venue\Domain\Models\VenueId;
use Venue\Domain\Models\VenueRepositoryInterface;

/**
 * Event が ID で参照する開催先・Media・リリース・楽曲・人物を引き、表示に必要な値へ組み立てる
 */
class EventAssembler
{
    public function __construct(
        private readonly VenueRepositoryInterface $venueRepository,
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly ReleaseRepositoryInterface $releaseRepository,
        private readonly ReleaseGroupRepositoryInterface $releaseGroupRepository,
        private readonly SongRepositoryInterface $songRepository,
        private readonly PersonRepositoryInterface $personRepository,
        private readonly PersonGroupRepositoryInterface $personGroupRepository,
    ) {
    }

    public function assemble(Event $event): AssembledEvent
    {
        $venueMap = $this->venueMap($event);
        $mediaMap = $this->mediaMap($event);
        $releaseMap = $this->releaseMap($event);
        $releaseGroupMap = $this->releaseGroupMap($releaseMap);
        $songMap = $this->songMap($event);
        $personMap = $this->personMap($event);
        $personGroupMap = $this->personGroupMap($event);

        $performances = [];
        foreach ($event->performances as $performance) {
            $performances[$performance->performanceId->value] = $this->toAssembledPerformance($performance, $songMap, $personMap, $personGroupMap);
        }

        return new AssembledEvent(
            $event->eventId->value,
            $event->title->value,
            $event->description->value,
            $event->type,
            $event->schedule->startOn?->format('Y-m-d'),
            $event->schedule->endOn?->format('Y-m-d'),
            $event->status,
            $event->isDisplay,
            $event->venues->toGeneric()->map(
                fn (EventVenueLink $link): AssembledVenue => $this->toAssembledVenue($venueMap[$link->venueId->value] ?? null),
            )->toArray(),
            $event->media->toGeneric()->map(
                fn (EventMediaLink $link): AssembledMedia => $this->toAssembledMedia($mediaMap[$link->mediaId->value] ?? null),
            )->toArray(),
            $event->releases->toGeneric()->map(
                fn (EventReleaseLink $link): AssembledRelease => $this->toAssembledRelease($releaseMap[$link->releaseId->value] ?? null, $releaseGroupMap),
            )->toArray(),
            $event->sources->toGeneric()->map(
                static fn (EventSource $source): AssembledSource => new AssembledSource($source->displayName->value, $source->url->value, $source->orderNo->value),
            )->toArray(),
            array_values($performances),
            $event->setlist->toGeneric()->map(
                static fn (SetlistItem $item): AssembledSetlistItem => new AssembledSetlistItem(
                    $item->setlistItemId->value,
                    $item->orderNo->value,
                    $item->label?->value,
                    array_map(static fn (PerformanceId $id): AssembledPerformance => $performances[$id->value], $item->performanceIds),
                ),
            )->toArray(),
        );
    }

    /** @return array<string, Venue> */
    private function venueMap(Event $event): array
    {
        $venueIds = $event->venues->toGeneric()->map(static fn (EventVenueLink $link): VenueId => $link->venueId)->toArray();

        $venueMap = [];
        foreach ($this->venueRepository->findByIds(...$venueIds) as $venue) {
            $venueMap[$venue->venueId->value] = $venue;
        }

        return $venueMap;
    }

    /** @return array<string, Media> */
    private function mediaMap(Event $event): array
    {
        $mediaIds = $event->media->toGeneric()->map(static fn (EventMediaLink $link): MediaId => $link->mediaId)->toArray();

        $mediaMap = [];
        foreach ($this->mediaRepository->findByIds(...$mediaIds) as $media) {
            $mediaMap[$media->mediaId->value] = $media;
        }

        return $mediaMap;
    }

    /** @return array<string, Release> */
    private function releaseMap(Event $event): array
    {
        $releaseIds = $event->releases->toGeneric()->map(static fn (EventReleaseLink $link): ReleaseId => $link->releaseId)->toArray();

        $releaseMap = [];
        foreach ($this->releaseRepository->findByIds(...$releaseIds) as $release) {
            $releaseMap[$release->releaseId->value] = $release;
        }

        return $releaseMap;
    }

    /**
     * @param array<string, Release> $releaseMap
     *
     * @return array<string, ReleaseGroup>
     */
    private function releaseGroupMap(array $releaseMap): array
    {
        $releaseGroupIds = [];
        foreach ($releaseMap as $release) {
            $releaseGroupIds[$release->releaseGroupId->value] = $release->releaseGroupId;
        }

        $releaseGroupMap = [];
        foreach ($this->releaseGroupRepository->findByIds(...array_values($releaseGroupIds)) as $releaseGroup) {
            $releaseGroupMap[$releaseGroup->releaseGroupId->value] = $releaseGroup;
        }

        return $releaseGroupMap;
    }

    /** @return array<string, Song> */
    private function songMap(Event $event): array
    {
        $songIds = [];
        foreach ($event->performances as $performance) {
            $songIds[$performance->songId->value] = $performance->songId;
        }

        $songMap = [];
        foreach ($this->songRepository->findByIds(...array_values($songIds)) as $song) {
            $songMap[$song->songId->value] = $song;
        }

        return $songMap;
    }

    /** @return array<string, Person> */
    private function personMap(Event $event): array
    {
        $personIds = [];
        foreach ($event->performances as $performance) {
            foreach ($performance->coVocalists as $coVocalist) {
                $personIds[$coVocalist->personId->value] = $coVocalist->personId;
            }
        }

        $personMap = [];
        foreach ($this->personRepository->findByIds(...array_values($personIds)) as $person) {
            $personMap[$person->personId->value] = $person;
        }

        return $personMap;
    }

    /** @return array<string, PersonGroup> */
    private function personGroupMap(Event $event): array
    {
        $personGroupIds = [];
        foreach ($event->performances as $performance) {
            foreach ($performance->coVocalists as $coVocalist) {
                if ($coVocalist->personGroupId !== null) {
                    $personGroupIds[$coVocalist->personGroupId->value] = $coVocalist->personGroupId;
                }
            }
        }

        $personGroupMap = [];
        foreach ($this->personGroupRepository->findByIds(...array_values($personGroupIds)) as $personGroup) {
            $personGroupMap[$personGroup->personGroupId->value] = $personGroup;
        }

        return $personGroupMap;
    }

    private function toAssembledVenue(?Venue $venue): AssembledVenue
    {
        // Event が成立している時点で参照先の開催先は存在する
        assert($venue instanceof Venue);

        return new AssembledVenue($venue->venueId->value, $venue->name->value, $venue->kind->value);
    }

    private function toAssembledMedia(?Media $media): AssembledMedia
    {
        // Event が成立している時点で参照先の Media は存在する
        assert($media instanceof Media);

        return new AssembledMedia(
            $media->mediaId->value,
            $media->title->value,
            $media->url->value,
            $media->publishedAt->value,
            $media->type->value,
            $media->isDisplay,
        );
    }

    /**
     * @param array<string, ReleaseGroup> $releaseGroupMap
     */
    private function toAssembledRelease(?Release $release, array $releaseGroupMap): AssembledRelease
    {
        // Event が成立している時点で参照先のリリースとその所属グループは存在する
        assert($release instanceof Release);
        $releaseGroup = $releaseGroupMap[$release->releaseGroupId->value] ?? null;
        assert($releaseGroup instanceof ReleaseGroup);

        return new AssembledRelease(
            $release->releaseId->value,
            $releaseGroup->releaseGroupId->value,
            $releaseGroup->title->value,
            $release->name->value,
            $release->releasedOn->value->format('Y-m-d'),
            $release->isDisplay && $releaseGroup->isDisplay,
            $release->formats->toArray(),
        );
    }

    /**
     * @param array<string, Song>        $songMap
     * @param array<string, Person>      $personMap
     * @param array<string, PersonGroup> $personGroupMap
     */
    private function toAssembledPerformance(SongPerformance $performance, array $songMap, array $personMap, array $personGroupMap): AssembledPerformance
    {
        $song = $songMap[$performance->songId->value] ?? null;
        // Event が成立している時点で参照先の楽曲は存在する
        assert($song instanceof Song);

        return new AssembledPerformance(
            $performance->performanceId->value,
            $song->songId->value,
            $song->title->value,
            $performance->orderNo->value,
            $performance->coVocalists->toGeneric()->map(static function (CoVocalist $coVocalist) use ($personMap, $personGroupMap): AssembledCoVocalist {
                $person = $personMap[$coVocalist->personId->value] ?? null;
                assert($person instanceof Person);

                return new AssembledCoVocalist(
                    $person->personId->value,
                    $person->name->value,
                    $coVocalist->creditName?->value,
                    self::toAssembledPersonGroup($coVocalist->personGroupId, $personGroupMap),
                    $coVocalist->orderNo->value,
                );
            })->toArray(),
        );
    }

    /**
     * @param array<string, PersonGroup> $personGroupMap
     */
    private static function toAssembledPersonGroup(?PersonGroupId $personGroupId, array $personGroupMap): ?AssembledPersonGroup
    {
        if ($personGroupId === null) {
            return null;
        }

        $personGroup = $personGroupMap[$personGroupId->value] ?? null;
        // 参照中のグループは削除できないため、出演グループは存在する
        assert($personGroup instanceof PersonGroup);

        return new AssembledPersonGroup($personGroup->personGroupId->value, $personGroup->name->value);
    }
}
