<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\V1\Event;

use Event\Domain\Models\EventStatus;
use Event\Route\EventRouteMap;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\Feature\Api\Admin\WithAuth;
use Tests\Support\Concerns\AssertsAuditLog;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class DeleteEventTest extends DatabaseTestCase
{
    use AssertsAuditLog;
    use EntityFactory;
    use EntityStore;
    use WithAuth;

    #[Test]
    public function canDeleteUnreferencedEvent(): void
    {
        $eventId = $this->generateUuid();
        $this->storeEvents($this->createEvent($eventId, title: '削除できる活動'));

        $this->withAuth()
            ->delete(route(EventRouteMap::Delete, $eventId))
            ->assertStatus(204);

        $this->assertAuditLogCount(1);
        $this->assertSame('削除できる活動', $this->findAuditLog(AuditAction::Delete, AuditTargetType::Event, $eventId)['snapshot']['title']);
    }

    #[Test]
    public function canDeletePostponedEventWithoutTargetLink(): void
    {
        $eventId = $this->generateUuid();
        $this->storeEvents($this->createEvent($eventId, title: '延期元活動', status: EventStatus::Postponed));

        $this->withAuth()->delete(route(EventRouteMap::Delete, $eventId))->assertStatus(204);
    }
}
