<?php

declare(strict_types=1);

namespace Tests\Unit\Event\Domain\Services;

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

        $event = $this->getInstance()->prepareForCreate($this->input());

        $this->assertSame(self::EVENT_ID, $event->eventId);
        $this->assertSame('テストライブ', $event->title);
    }

    #[Test]
    public function prepareForUpdateKeepsEventIdWithPerformanceAndSetlist(): void
    {
        $this->generator->shouldNotReceive('generate');

        $event = $this->getInstance()->prepareForUpdate(self::EVENT_ID, $this->input([
            'performances' => [self::performance(self::PERFORMANCE_ID, 1)],
            'setlist' => [self::setlistItem(1, '本編', [self::performance(self::PERFORMANCE_ID, 1)])],
        ]));

        $this->assertSame(self::EVENT_ID, $event->eventId);
        $this->assertSame(self::PERFORMANCE_ID, $event->setlist[0]['performances'][0]['performance_id']);
    }

    #[Test]
    public function rejectsSetlistForExhibition(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->getInstance()->prepareForUpdate(self::EVENT_ID, $this->input([
            'typeValue' => EventType::Exhibition->value,
            'setlist' => [self::setlistItem(1, '展示作品', [])],
        ]));
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

        $this->getInstance()->prepareForUpdate(self::EVENT_ID, $this->input(['statusValue' => $status->value, ...$contents]));
    }

    #[Test]
    public function acceptsCancelledEventWithoutPerformancesAndSetlist(): void
    {
        $this->expectNotToPerformAssertions();

        $this->getInstance()->prepareForUpdate(self::EVENT_ID, $this->input(['statusValue' => EventStatus::Cancelled->value]));
    }

    #[Test]
    public function rejectsEndDateWithoutStartDate(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->getInstance()->prepareForUpdate(self::EVENT_ID, $this->input(['schedule' => ['startOn' => null, 'endOn' => '2026-10-03']]));
    }

    #[Test]
    public function rejectsEndDateBeforeStartDate(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->getInstance()->prepareForUpdate(self::EVENT_ID, $this->input(['schedule' => ['startOn' => '2026-10-03', 'endOn' => '2026-10-01']]));
    }

    #[Test]
    public function rejectsDuplicatedVenue(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $venueId = 'EEEEEEEE-EEEE-EEEE-EEEE-EEEEEEEEEEEE';
        $this->getInstance()->prepareForUpdate(self::EVENT_ID, $this->input(['venueIds' => [$venueId, $venueId]]));
    }

    #[Test]
    public function rejectsSetlistItemWithoutLabelAndPerformance(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->getInstance()->prepareForUpdate(self::EVENT_ID, $this->input(['setlist' => [self::setlistItem(1, null, [])]]));
    }

    #[Test]
    public function rejectsSetlistReferencingUnknownPerformance(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->getInstance()->prepareForUpdate(self::EVENT_ID, $this->input([
            'performances' => [self::performance(self::PERFORMANCE_ID, 1)],
            'setlist' => [self::setlistItem(1, null, [self::performance(self::OTHER_PERFORMANCE_ID, 1)])],
        ]));
    }

    #[Test]
    public function rejectsPerformanceReferencedFromMultipleSetlistItems(): void
    {
        $this->expectException(BusinessRuleViolationException::class);

        $this->getInstance()->prepareForUpdate(self::EVENT_ID, $this->input([
            'performances' => [self::performance(self::PERFORMANCE_ID, 1)],
            'setlist' => [
                self::setlistItem(1, null, [self::performance(self::PERFORMANCE_ID, 1)]),
                self::setlistItem(2, null, [self::performance(self::PERFORMANCE_ID, 1)]),
            ],
        ]));
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function input(array $overrides = []): array
    {
        return [
            'title' => 'テストライブ',
            'description' => '',
            'typeValue' => EventType::Live->value,
            'schedule' => ['startOn' => '2026-10-01', 'endOn' => null],
            'statusValue' => EventStatus::Normal->value,
            'isDisplay' => true,
            'venueIds' => [],
            'mediaIds' => [],
            'sources' => [],
            'performances' => [],
            'setlist' => [],
            ...$overrides,
        ];
    }

    /** @return array<string, mixed> */
    private static function performance(string $performanceId, int $orderNo): array
    {
        return ['performanceId' => $performanceId, 'songId' => self::SONG_ID, 'songTitle' => '披露曲', 'orderNo' => $orderNo, 'coVocalists' => []];
    }

    /**
     * @param list<array<string, mixed>> $performances
     *
     * @return array<string, mixed>
     */
    private static function setlistItem(int $orderNo, ?string $label, array $performances): array
    {
        return ['setlistItemId' => sprintf('FFFFFFFF-FFFF-FFFF-FFFF-%012d', $orderNo), 'orderNo' => $orderNo, 'label' => $label, 'performances' => $performances];
    }

    private function getInstance(): EventIntegrityService
    {
        return new EventIntegrityService($this->generator);
    }
}
