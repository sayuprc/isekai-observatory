<?php

declare(strict_types=1);

namespace Event\Domain\Models;

interface EventRepositoryInterface
{
    public function find(EventId $eventId): ?Event;

    public function save(Event $event): Event;

    public function delete(EventId $eventId): void;
}
