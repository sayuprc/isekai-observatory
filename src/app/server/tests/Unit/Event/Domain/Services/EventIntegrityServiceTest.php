<?php

declare(strict_types=1);

namespace Tests\Unit\Event\Domain\Services;

use Event\Domain\Models\Event;
use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;
use Event\Domain\Services\EventIntegrityService;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Tests\TestCase;

/**
 * @phpstan-import-type _songPerformanceInput from \Event\Domain\Models\Performances\SongPerformances
 * @phpstan-import-type _setlistItemInput from \Event\Domain\Models\Setlist\Setlist
 * @phpstan-import-type _eventSourceInput from \Event\Domain\Models\Sources\EventSources
 */
class EventIntegrityServiceTest extends TestCase
{
    private const string EVENT_ID = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

    private const string SONG_ID = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';

    private const string PERFORMANCE_ID = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC';

    private const string OTHER_PERFORMANCE_ID = 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD';

    private const string VENUE_ID = 'EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE';

    private const string OTHER_VENUE_ID = '99999999-9999-9999-9999-999999999999';

    private MockInterface&UuidGeneratorInterface $generator;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = Mockery::mock(UuidGeneratorInterface::class);
    }

    #[Test]
    public function prepareForCreateAssignsGeneratedEventId(): void
    {
        $this->generator->shouldReceive('generate')
            ->with()
            ->andReturn(self::EVENT_ID)
            ->once();

        $event = $this->getInstance()->prepareForCreate(...$this->input(venues: [['venueId' => self::VENUE_ID, 'orderNo' => 1]]));

        $this->assertSame(self::EVENT_ID, $event->eventId->value);
        $this->assertSame('テストライブ', $event->title->value);
        $this->assertSame(self::VENUE_ID, $event->venues[0]->venueId->value);
        $this->assertSame(1, $event->venues[0]->orderNo->value);
    }

    #[Test]
    public function prepareForUpdateKeepsEventIdWithPerformanceAndSetlist(): void
    {
        $this->generator->shouldNotReceive('generate');

        $event = $this->prepareForUpdate(
            performances: [self::performance(self::PERFORMANCE_ID, 1)],
            setlist: [self::setlistItem(1, '本編', [self::PERFORMANCE_ID])],
        );

        $this->assertSame(self::EVENT_ID, $event->eventId->value);
        $this->assertSame(self::SONG_ID, $event->performances[0]->songId->value);
        $this->assertSame(self::PERFORMANCE_ID, $event->setlist[0]->performanceIds[0]->value);
    }

    #[Test]
    public function rejectsSetlistForExhibition(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->prepareForUpdate(type: EventType::Exhibition->value, setlist: [self::setlistItem(1, '展示作品', [])]);
    }

    #[Test]
    public function rejectsSetlistForRadio(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->prepareForUpdate(type: EventType::Radio->value, setlist: [self::setlistItem(1, 'オープニングトーク', [])]);
    }

    /**
     * @return iterable<string, array{EventStatus, array<string, mixed>}>
     */
    public static function heldOnlyContents(): iterable
    {
        yield '延期で楽曲披露あり' => [EventStatus::Postponed, ['performances' => [self::performance(self::PERFORMANCE_ID, 1)]]];
        yield '中止で楽曲披露あり' => [EventStatus::Cancelled, ['performances' => [self::performance(self::PERFORMANCE_ID, 1)]]];
        yield '延期でセットリストあり' => [EventStatus::Postponed, ['setlist' => [self::setlistItem(1, 'オープニング', [])]]];
        yield '中止でセットリストあり' => [EventStatus::Cancelled, ['setlist' => [self::setlistItem(1, 'オープニング', [])]]];
    }

    /**
     * @param array<string, mixed> $contents
     */
    #[Test]
    #[DataProvider('heldOnlyContents')]
    public function rejectsPerformancesAndSetlistForPostponedOrCancelledEvent(EventStatus $status, array $contents): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->prepareForUpdate(...[...$contents, 'status' => $status->value]);
    }

    #[Test]
    public function acceptsCancelledEventWithoutPerformancesAndSetlist(): void
    {
        $event = $this->prepareForUpdate(status: EventStatus::Cancelled->value);

        $this->assertSame(EventStatus::Cancelled, $event->status);
    }

    #[Test]
    public function rejectsEndDateWithoutStartDate(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->prepareForUpdate(schedule: ['startOn' => null, 'endOn' => '2026-10-03']);
    }

    #[Test]
    public function rejectsEndDateBeforeStartDate(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->prepareForUpdate(schedule: ['startOn' => '2026-10-03', 'endOn' => '2026-10-01']);
    }

    #[Test]
    public function rejectsDuplicatedVenue(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->prepareForUpdate(venues: [['venueId' => self::VENUE_ID, 'orderNo' => 1], ['venueId' => self::VENUE_ID, 'orderNo' => 2]]);
    }

    #[Test]
    public function rejectsDuplicatedVenueOrder(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->prepareForUpdate(venues: [['venueId' => self::VENUE_ID, 'orderNo' => 1], ['venueId' => self::OTHER_VENUE_ID, 'orderNo' => 1]]);
    }

    #[Test]
    public function rejectsSetlistItemWithoutLabelAndPerformance(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->prepareForUpdate(setlist: [self::setlistItem(1, null, [])]);
    }

    #[Test]
    public function rejectsSetlistReferencingUnknownPerformance(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->prepareForUpdate(
            performances: [self::performance(self::PERFORMANCE_ID, 1)],
            setlist: [self::setlistItem(1, null, [self::OTHER_PERFORMANCE_ID])],
        );
    }

    #[Test]
    public function rejectsPerformanceReferencedFromMultipleSetlistItems(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->prepareForUpdate(
            performances: [self::performance(self::PERFORMANCE_ID, 1)],
            setlist: [
                self::setlistItem(1, null, [self::PERFORMANCE_ID]),
                self::setlistItem(2, null, [self::PERFORMANCE_ID]),
            ],
        );
    }

    /**
     * @param array{startOn: ?string, endOn: ?string}    $schedule
     * @param list<array{venueId: string, orderNo: int}> $venues
     * @param list<_songPerformanceInput>                $performances
     * @param list<_setlistItemInput>                    $setlist
     */
    private function prepareForUpdate(
        int $type = EventType::Live->value,
        array $schedule = ['startOn' => '2026-10-01', 'endOn' => null],
        int $status = EventStatus::Normal->value,
        array $venues = [],
        array $performances = [],
        array $setlist = [],
    ): Event {
        return $this->getInstance()->prepareForUpdate(self::EVENT_ID, ...$this->input($type, $schedule, $status, $venues, $performances, $setlist));
    }

    /**
     * @param array{startOn: ?string, endOn: ?string}    $schedule
     * @param list<array{venueId: string, orderNo: int}> $venues
     * @param list<_songPerformanceInput>                $performances
     * @param list<_setlistItemInput>                    $setlist
     *
     * @return array{title: string, description: string, type: int, schedule: array{startOn: ?string, endOn: ?string}, status: int, isDisplay: bool, venues: list<array{venueId: string, orderNo: int}>, media: list<array{mediaId: string, orderNo: int}>, sources: list<_eventSourceInput>, performances: list<_songPerformanceInput>, setlist: list<_setlistItemInput>}
     */
    private function input(
        int $type = EventType::Live->value,
        array $schedule = ['startOn' => '2026-10-01', 'endOn' => null],
        int $status = EventStatus::Normal->value,
        array $venues = [],
        array $performances = [],
        array $setlist = [],
    ): array {
        return [
            'title' => 'テストライブ',
            'description' => '',
            'type' => $type,
            'schedule' => $schedule,
            'status' => $status,
            'isDisplay' => true,
            'venues' => $venues,
            'media' => [],
            'sources' => [],
            'performances' => $performances,
            'setlist' => $setlist,
        ];
    }

    /**
     * @return _songPerformanceInput
     */
    private static function performance(string $performanceId, int $orderNo): array
    {
        return ['performanceId' => $performanceId, 'songId' => self::SONG_ID, 'orderNo' => $orderNo, 'coVocalists' => []];
    }

    /**
     * @param list<string> $performanceIds
     *
     * @return _setlistItemInput
     */
    private static function setlistItem(int $orderNo, ?string $label, array $performanceIds): array
    {
        return [
            'setlistItemId' => sprintf('FFFFFFFF-FFFF-FFFF-FFFF-%012d', $orderNo),
            'orderNo' => $orderNo,
            'label' => $label,
            'performanceIds' => $performanceIds,
        ];
    }

    private function getInstance(): EventIntegrityService
    {
        return new EventIntegrityService($this->generator);
    }
}
