<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Event;

use Event\Domain\Models\Event;
use Event\Route\EventRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityStore;

class DeleteEventTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canDeleteUnreferencedEvent(): void
    {
        $eventId = $this->generateUuid();
        $this->storeEvents($this->event($eventId));

        $this->withAuth()
            ->delete(route(EventRouteMap::Delete, $eventId))
            ->assertStatus(204);

        $this->assertAuditLogCount(1);
        $this->assertSame('削除できる活動', $this->findAuditLog(AuditAction::Delete, AuditTargetType::Event, $eventId)['snapshot']['title']);
    }

    #[Test]
    public function rejectsDeletingAnEventInPostponementChain(): void
    {
        $targetId = $this->generateUuid();
        $sourceId = $this->generateUuid();
        $this->storeEvents(
            $this->event($targetId),
            $this->event($sourceId, 1, $targetId),
        );

        $client = $this->withAuth();
        $client->delete(route(EventRouteMap::Delete, $targetId))
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
        $client->delete(route(EventRouteMap::Delete, $sourceId))
            ->assertStatus(400)
            ->assertJsonPath('code', 'business_rule_violation');
    }

    private function event(string $eventId, int $status = 0, ?string $postponedToEventId = null): Event
    {
        return Event::fromInput($eventId, [
            'title' => $status === 0 ? '削除できる活動' : '延期元活動',
            'description' => null,
            'typeValue' => 1,
            'schedule' => ['type' => 2, 'startDate' => '2026-10-01', 'endDate' => null, 'startDateTime' => null, 'endDateTime' => null, 'timeZone' => null],
            'statusValue' => $status === 0 ? null : 1,
            'postponedToEventId' => $postponedToEventId,
            'isDisplay' => true,
            'venueIds' => [],
            'mediaIds' => [],
            'sources' => [],
            'performances' => [],
            'setlist' => [],
        ]);
    }
}
