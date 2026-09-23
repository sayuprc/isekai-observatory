<?php

declare(strict_types=1);

namespace Event\Application\Admin\Assemble;

use Event\Domain\Models\Event;
use Event\Domain\Models\Media\EventMediaLink;
use Event\Domain\Models\Performances\CoVocalist;
use Event\Domain\Models\Performances\PerformanceId;
use Event\Domain\Models\Performances\SongPerformance;
use Event\Domain\Models\Setlist\SetlistItem;
use Event\Domain\Models\Sources\EventSource;
use Event\Domain\Models\Venues\EventVenueLink;
use Media\Domain\Models\Media;
use Media\Domain\Models\MediaRepositoryInterface;
use Person\Domain\Models\Person;
use Person\Domain\Models\PersonRepositoryInterface;
use Song\Domain\Models\SongRepositoryInterface;
use Venue\Domain\Models\VenueRepositoryInterface;

/**
 * Event が ID で参照する開催先・Media・楽曲・人物を引き、表示に必要な値へ組み立てる
 */
class EventAssembler
{
    public function __construct(
        private readonly VenueRepositoryInterface $venueRepository,
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly SongRepositoryInterface $songRepository,
        private readonly PersonRepositoryInterface $personRepository,
    ) {
    }

    public function assemble(Event $event): AssembledEvent
    {
        $personMap = $this->personMap($event);
        $performances = [];
        foreach ($event->performances as $performance) {
            $performances[$performance->performanceId->value] = $this->toAssembledPerformance($performance, $personMap);
        }

        return new AssembledEvent(
            $event->eventId->value,
            $event->title->value,
            $event->description->value,
            $event->type->value,
            $event->schedule->startOn?->format('Y-m-d'),
            $event->schedule->endOn?->format('Y-m-d'),
            $event->status->value,
            $event->isDisplay,
            $event->venues->toGeneric()->map($this->toAssembledVenue(...))->toArray(),
            $this->toAssembledMediaList($event),
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

    private function toAssembledVenue(EventVenueLink $link): AssembledVenue
    {
        $venue = $this->venueRepository->find($link->venueId);
        // Event が成立している時点で参照先の開催先は存在する
        assert(! is_null($venue));

        return new AssembledVenue($venue->venueId->value, $venue->name->value, $venue->kind->getName(), $venue->kind->value);
    }

    /** @return array<int, AssembledMedia> */
    private function toAssembledMediaList(Event $event): array
    {
        $mediaIds = $event->media->toGeneric()->map(static fn (EventMediaLink $link) => $link->mediaId)->toArray();
        $mediaMap = [];
        foreach ($mediaIds === [] ? [] : $this->mediaRepository->findByIds(...$mediaIds) as $media) {
            $mediaMap[$media->mediaId->value] = $media;
        }

        return $event->media->toGeneric()->map(static function (EventMediaLink $link) use ($mediaMap): AssembledMedia {
            $media = $mediaMap[$link->mediaId->value] ?? null;
            assert($media instanceof Media);

            return new AssembledMedia(
                $media->mediaId->value,
                $media->title->value,
                $media->url->value,
                $media->publishedAt->value,
                $media->type->getName(),
                $media->type->value,
                $media->isDisplay,
            );
        })->toArray();
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
        foreach ($personIds === [] ? [] : $this->personRepository->findByIds(...array_values($personIds)) as $person) {
            $personMap[$person->personId->value] = $person;
        }

        return $personMap;
    }

    /** @param array<string, Person> $personMap */
    private function toAssembledPerformance(SongPerformance $performance, array $personMap): AssembledPerformance
    {
        $song = $this->songRepository->find($performance->songId);
        // Event が成立している時点で参照先の楽曲は存在する
        assert(! is_null($song));

        return new AssembledPerformance(
            $performance->performanceId->value,
            $song->songId->value,
            $song->title->value,
            $performance->orderNo->value,
            $performance->coVocalists->toGeneric()->map(static function (CoVocalist $coVocalist) use ($personMap): AssembledCoVocalist {
                $person = $personMap[$coVocalist->personId->value] ?? null;
                assert($person instanceof Person);

                return new AssembledCoVocalist($person->personId->value, $person->name->value, $coVocalist->creditName?->value, $coVocalist->orderNo->value);
            })->toArray(),
        );
    }
}
