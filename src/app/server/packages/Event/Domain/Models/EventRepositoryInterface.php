<?php

declare(strict_types=1);

namespace Event\Domain\Models;

interface EventRepositoryInterface
{
    public function find(EventId $eventId): ?Event;

    /**
     * @return array<int, Event>
     */
    public function findByIds(EventId ...$eventIds): array;

    public function save(Event $event): Event;

    public function delete(EventId $eventId): void;
}
