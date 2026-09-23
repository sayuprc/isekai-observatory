<?php

declare(strict_types=1);

namespace Event\Application\Viewer\UseCase\List;

use Event\Domain\Criteria\EventSearchCriteria;
use Event\Domain\Models\Event as EventModel;
use Event\Domain\Models\EventRepositoryInterface;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;

readonly class ListUseCase
{
    private const int DEFAULT_LIMIT = 50;

    private const int MAX_LIMIT = 50;

    public function __construct(private EventRepositoryInterface $repository)
    {
    }

    public function handle(ListInputData $inputData): ListOutputData
    {
        $limit = min(self::MAX_LIMIT, $inputData->limit ?? self::DEFAULT_LIMIT);
        $perPage = $this->perPage($limit);
        [$page, $offset] = $this->decodeCursor($inputData->cursor);
        $criteria = new EventSearchCriteria(null, null, null, true, 'schedule', Order::Asc, $page, $perPage);
        $events = $this->repository->search($criteria);
        $maxPage = $this->repository->maxPage($criteria);
        if ($offset >= count($events) && $page < $maxPage) {
            $page++;
            $offset = 0;
            $criteria = new EventSearchCriteria(null, null, null, true, 'schedule', Order::Asc, $page, $perPage);
            $events = $this->repository->search($criteria);
        }
        $pageEvents = array_slice($events, $offset, $limit);
        $hasNext = $offset + count($pageEvents) < count($events) || $page < $maxPage;
        $events = $pageEvents;
        $events = array_map(fn (EventModel $event): EventModel => $this->repository->find($event->eventId) ?? $event, $events);

        return new ListOutputData($events, $hasNext ? $this->encodeCursor($page, $offset + count($pageEvents)) : null);
    }

    /** @return array{0: int, 1: int} */
    private function decodeCursor(?string $cursor): array
    {
        if ($cursor === null) {
            return [1, 0];
        }
        $decoded = base64_decode($cursor, true);
        if (! is_string($decoded)) {
            return [1, 0];
        }
        $parts = explode(':', $decoded, 2);
        if (count($parts) === 2 && ctype_digit($parts[0]) && ctype_digit($parts[1])) {
            return [max(1, (int)$parts[0]), max(0, (int)$parts[1])];
        }

        return ctype_digit($decoded) ? [max(1, (int)$decoded), 0] : [1, 0];
    }

    private function encodeCursor(int $page, int $offset): string
    {
        return base64_encode($page . ':' . $offset);
    }

    private function perPage(int $limit): PerPage
    {
        return match (true) {
            $limit <= 25 => PerPage::TwentyFive,
            $limit <= 50 => PerPage::Fifty,
            default => PerPage::Hundred,
        };
    }
}
