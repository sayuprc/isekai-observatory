<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Event;

use Event\Route\EventRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Song\Domain\Models\SongType;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class UpdateEventTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canUpdateEventWithPerformanceAndSetlist(): void
    {
        $songId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));
        $eventId = $this->generateUuid();
        $this->storeEvents($this->createEvent($eventId));

        $performanceId = $this->generateUuid();
        $this->withAuth()
            ->putJson(route(EventRouteMap::Update, ['eventId' => $eventId]), [
                'title' => '更新後のライブ',
                'description' => '説明',
                'typeValue' => 1,
                'schedule' => ['startOn' => '2026-10-01', 'endOn' => '2026-10-02'],
                'statusValue' => 1,
                'isDisplay' => true,
                'venues' => [],
                'media' => [],
                'sources' => [],
                'performances' => [['performanceId' => $performanceId, 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => []]],
                'setlist' => [['setlistItemId' => $this->generateUuid(), 'orderNo' => 1, 'label' => null, 'performanceIds' => [$performanceId]]],
            ])
            ->assertStatus(200)
            ->assertJsonPath('event.eventId', $eventId)
            ->assertJsonPath('event.title', '更新後のライブ')
            ->assertJsonPath('event.schedule.endOn', '2026-10-02')
            ->assertJsonPath('event.setlist.0.performances.0.performanceId', $performanceId);

        $this->assertSame('更新後のライブ', $this->findAuditLog(AuditAction::Update, AuditTargetType::Event, $eventId)['snapshot']['title']);
    }

    #[Test]
    public function rejectsPerformanceWhenCancelling(): void
    {
        $songId = $this->generateUuid();
        $this->storeSongs($this->createSong($songId, '披露曲', '説明', SongType::Original, true, 1));
        $eventId = $this->generateUuid();
        $this->storeEvents($this->createEvent($eventId));

        $this->withAuth()
            ->putJson(route(EventRouteMap::Update, ['eventId' => $eventId]), [
                'title' => '中止されたライブ',
                'description' => '',
                'typeValue' => 1,
                'schedule' => ['startOn' => '2026-10-01', 'endOn' => null],
                'statusValue' => 3,
                'isDisplay' => true,
                'venues' => [],
                'media' => [],
                'sources' => [],
                'performances' => [['performanceId' => $this->generateUuid(), 'songId' => $songId, 'orderNo' => 1, 'coVocalists' => []]],
                'setlist' => [],
            ])
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
    }
}
