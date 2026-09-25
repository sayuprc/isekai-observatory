<?php

declare(strict_types=1);

namespace Event\Domain\Services;

use Event\Domain\Models\Event;
use Event\Domain\Models\EventDescription;
use Event\Domain\Models\EventId;
use Event\Domain\Models\EventSchedule;
use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventTitle;
use Event\Domain\Models\EventType;
use Event\Domain\Models\Media\EventMediaLinks;
use Event\Domain\Models\Performances\SongPerformances;
use Event\Domain\Models\Setlist\Setlist;
use Event\Domain\Models\Sources\EventSources;
use Event\Domain\Models\Venues\EventVenueLinks;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;

/**
 * Event 集約内でしか判断できない不変条件を検証する。
 * 外部集約の存在確認は FK に委ね、ここでは関係の形だけを扱う。
 *
 * @phpstan-import-type SongPerformanceInput from \Event\Domain\Models\Performances\SongPerformances
 * @phpstan-import-type SetlistItemInput from \Event\Domain\Models\Setlist\Setlist
 * @phpstan-import-type EventSourceInput from \Event\Domain\Models\Sources\EventSources
 */
readonly class EventIntegrityService
{
    public function __construct(private UuidGeneratorInterface $generator)
    {
    }

    /**
     * @param array{startOn: ?string, endOn: ?string}    $schedule
     * @param list<array{venueId: string, orderNo: int}> $venues
     * @param list<array{mediaId: string, orderNo: int}> $media
     * @param list<EventSourceInput>                     $sources
     * @param list<SongPerformanceInput>                 $performances
     * @param list<SetlistItemInput>                     $setlist
     *
     * @throws BusinessRuleViolationException
     */
    public function prepareForCreate(
        string $title,
        string $description,
        int $type,
        array $schedule,
        int $status,
        bool $isDisplay,
        array $venues,
        array $media,
        array $sources,
        array $performances,
        array $setlist,
    ): Event {
        return $this->build(
            $this->generator->generate(),
            $title,
            $description,
            $type,
            $schedule,
            $status,
            $isDisplay,
            $venues,
            $media,
            $sources,
            $performances,
            $setlist,
        );
    }

    /**
     * @param array{startOn: ?string, endOn: ?string}    $schedule
     * @param list<array{venueId: string, orderNo: int}> $venues
     * @param list<array{mediaId: string, orderNo: int}> $media
     * @param list<EventSourceInput>                     $sources
     * @param list<SongPerformanceInput>                 $performances
     * @param list<SetlistItemInput>                     $setlist
     *
     * @throws BusinessRuleViolationException
     */
    public function prepareForUpdate(
        string $eventId,
        string $title,
        string $description,
        int $type,
        array $schedule,
        int $status,
        bool $isDisplay,
        array $venues,
        array $media,
        array $sources,
        array $performances,
        array $setlist,
    ): Event {
        return $this->build(
            $eventId,
            $title,
            $description,
            $type,
            $schedule,
            $status,
            $isDisplay,
            $venues,
            $media,
            $sources,
            $performances,
            $setlist,
        );
    }

    /**
     * @param array{startOn: ?string, endOn: ?string}    $schedule
     * @param list<array{venueId: string, orderNo: int}> $venues
     * @param list<array{mediaId: string, orderNo: int}> $media
     * @param list<EventSourceInput>                     $sources
     * @param list<SongPerformanceInput>                 $performances
     * @param list<SetlistItemInput>                     $setlist
     *
     * @throws BusinessRuleViolationException
     */
    private function build(
        string $eventId,
        string $title,
        string $description,
        int $type,
        array $schedule,
        int $status,
        bool $isDisplay,
        array $venues,
        array $media,
        array $sources,
        array $performances,
        array $setlist,
    ): Event {
        $event = new Event(
            new EventId($eventId),
            new EventTitle($title),
            new EventDescription($description),
            EventType::from($type),
            EventSchedule::fromArray($schedule['startOn'], $schedule['endOn']),
            EventStatus::from($status),
            $isDisplay,
            EventVenueLinks::fromArray($venues),
            EventMediaLinks::fromArray($media),
            EventSources::fromArray($sources),
            SongPerformances::fromArray($performances),
            Setlist::fromArray($setlist),
        );

        $this->assertSetlistAllowed($event);
        $this->assertSetlistReferencesPerformances($event);

        return $event;
    }

    /**
     * @throws BusinessRuleViolationException
     */
    private function assertSetlistAllowed(Event $event): void
    {
        if (! $event->type->allowsSetlist() && count($event->setlist) > 0) {
            throw new BusinessRuleViolationException('ライブまたは配信以外のイベントにはセットリストを設定できません');
        }

        if (! $event->status->allowsPerformances() && (count($event->performances) > 0 || count($event->setlist) > 0)) {
            throw new BusinessRuleViolationException('延期または中止されたイベントには楽曲披露とセットリストを設定できません');
        }
    }

    /**
     * @throws BusinessRuleViolationException
     */
    private function assertSetlistReferencesPerformances(Event $event): void
    {
        foreach ($event->setlist->referencedPerformanceIds() as $performanceId) {
            if (! $event->performances->contains($performanceId)) {
                throw new BusinessRuleViolationException('セットリストが存在しない楽曲披露を参照しています');
            }
        }
    }
}
