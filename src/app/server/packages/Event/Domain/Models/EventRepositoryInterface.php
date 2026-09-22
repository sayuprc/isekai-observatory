<?php

declare(strict_types=1);

namespace Event\Domain\Models;

use Event\Domain\Criteria\EventSearchCriteria;

interface EventRepositoryInterface
{
    /** @return list<Event> */
    public function search(EventSearchCriteria $criteria): array;

    public function maxPage(EventSearchCriteria $criteria): int;

    public function find(string $eventId): ?Event;

    public function save(Event $event): Event;

    public function isReferenced(string $eventId): bool;

    public function delete(string $eventId): void;
}
