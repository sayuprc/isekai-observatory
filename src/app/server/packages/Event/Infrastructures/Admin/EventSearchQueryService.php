<?php

declare(strict_types=1);

namespace Event\Infrastructures\Admin;

use Emonkak\Orm\SelectBuilder;
use Event\Application\Admin\Query\EventSearchQueryServiceInterface;
use Event\Application\Admin\Query\EventSummary;
use Event\Domain\Criteria\EventSearchCriteria;
use Event\Domain\Criteria\Sort;
use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Support\Infrastructures\Database\SqlHelper;

readonly class EventSearchQueryService implements EventSearchQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function search(EventSearchCriteria $criteria): array
    {
        $sort = match ($criteria->sort) {
            Sort::Schedule => 'start_on',
            Sort::Title => 'title_lower',
        };

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->paginate(
                $this->buildSearchQuery($criteria)
                    ->withSelect(['event_id', 'title', 'type', 'start_on', 'end_on', 'status', 'is_display'])
                    ->orderBy($sort, $criteria->order->value)
                    ->orderBy('event_id'),
                $criteria->page,
                $criteria->perPage,
            ),
        );

        return array_map(
            fn (array $row): EventSummary => new EventSummary(
                $this->converter->toUuid(Row::string($row, 'event_id')),
                Row::string($row, 'title'),
                EventType::from(Row::int($row, 'type')),
                Row::nullableString($row, 'start_on'),
                Row::nullableString($row, 'end_on'),
                EventStatus::from(Row::int($row, 'status')),
                Row::bool($row, 'is_display'),
            ),
            $rows,
        );
    }

    #[Override]
    public function maxPage(EventSearchCriteria $criteria): int
    {
        return $this->queryFactory->maxPage($this->buildSearchQuery($criteria), $criteria->perPage);
    }

    private function buildSearchQuery(EventSearchCriteria $criteria): SelectBuilder
    {
        $query = $this->queryFactory->select()->from('events');

        if ($criteria->title->isPresent()) {
            $query = $query->where(
                'title_lower',
                'LIKE',
                SqlHelper::containsPattern(mb_strtolower($criteria->title->get())),
            );
        }

        if ($criteria->type->isPresent()) {
            $query = $query->where('type', '=', $criteria->type->get()->value);
        }

        if ($criteria->status->isPresent()) {
            $query = $query->where('status', '=', $criteria->status->get()->value);
        }

        if ($criteria->isDisplay->isPresent()) {
            $query = $query->where('is_display', '=', $criteria->isDisplay->get());
        }

        return $query;
    }
}
