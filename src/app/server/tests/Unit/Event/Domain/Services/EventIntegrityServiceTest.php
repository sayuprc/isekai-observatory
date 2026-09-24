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

class EventIntegrityServiceTest extends TestCase
{
    private const string EVENT_ID = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

    private const string SONG_ID = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';

    private const string PERFORMANCE_ID = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC';

    private const string OTHER_PERFORMANCE_ID = 'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD';

    private const string VENUE_ID = 'EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE';

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

        $event = $this->getInstance()->prepareForCreate(...$this->input(venueIds: [self::VENUE_ID]));

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

        $this->prepareForUpdate(venueIds: [self::VENUE_ID, self::VENUE_ID]);
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
     * @param array{startOn: ?string, endOn: ?string}                                                                                                         $schedule
     * @param list<string>                                                                                                                                    $venueIds
     * @param list<array{performanceId: string, songId: string, orderNo: int, coVocalists: list<array{personId: string, creditName: ?string, orderNo: int}>}> $performances
     * @param list<array{setlistItemId: string, orderNo: int, label: ?string, performanceIds: list<string>}>                                                  $setlist
     */
    private function prepareForUpdate(
        int $type = EventType::Live->value,
        array $schedule = ['startOn' => '2026-10-01', 'endOn' => null],
        int $status = EventStatus::Normal->value,
        array $venueIds = [],
        array $performances = [],
        array $setlist = [],
    ): Event {
        return $this->getInstance()->prepareForUpdate(self::EVENT_ID, ...$this->input($type, $schedule, $status, $venueIds, $performances, $setlist));
    }

    /**
     * @param array{startOn: ?string, endOn: ?string}                                                                                                         $schedule
     * @param list<string>                                                                                                                                    $venueIds
     * @param list<array{performanceId: string, songId: string, orderNo: int, coVocalists: list<array{personId: string, creditName: ?string, orderNo: int}>}> $performances
     * @param list<array{setlistItemId: string, orderNo: int, label: ?string, performanceIds: list<string>}>                                                  $setlist
     *
     * @return array{title: string, description: string, type: int, schedule: array{startOn: ?string, endOn: ?string}, status: int, isDisplay: bool, venueIds: list<string>, mediaIds: list<string>, sources: list<array{displayName: string, url: string, orderNo: int}>, performances: list<array{performanceId: string, songId: string, orderNo: int, coVocalists: list<array{personId: string, creditName: ?string, orderNo: int}>}>, setlist: list<array{setlistItemId: string, orderNo: int, label: ?string, performanceIds: list<string>}>}
     */
    private function input(
        int $type = EventType::Live->value,
        array $schedule = ['startOn' => '2026-10-01', 'endOn' => null],
        int $status = EventStatus::Normal->value,
        array $venueIds = [],
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
            'venueIds' => $venueIds,
            'mediaIds' => [],
            'sources' => [],
            'performances' => $performances,
            'setlist' => $setlist,
        ];
    }

    /**
     * @return array{performanceId: string, songId: string, orderNo: int, coVocalists: list<array{personId: string, creditName: ?string, orderNo: int}>}
     */
    private static function performance(string $performanceId, int $orderNo): array
    {
        return ['performanceId' => $performanceId, 'songId' => self::SONG_ID, 'orderNo' => $orderNo, 'coVocalists' => []];
    }

    /**
     * @param list<string> $performanceIds
     *
     * @return array{setlistItemId: string, orderNo: int, label: ?string, performanceIds: list<string>}
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
