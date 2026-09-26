<?php

declare(strict_types=1);

namespace Venue\Infrastructures;

use Emonkak\Orm\SelectBuilder;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Support\Infrastructures\Database\SqlHelper;
use UnexpectedValueException;
use Venue\Domain\Criteria\VenueSearchCriteria;
use Venue\Domain\Models\Venue;
use Venue\Domain\Models\VenueId;
use Venue\Domain\Models\VenueKind;
use Venue\Domain\Models\VenueRepositoryInterface;

readonly class VenueRepository implements VenueRepositoryInterface
{
    private const string TABLE = 'venues';

    /** @var list<string> */
    private const array COLUMNS = ['venue_id', 'name', 'kind'];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function search(VenueSearchCriteria $criteria): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->paginate(
                $this->buildSearchQuery($criteria)
                    ->withSelect(self::COLUMNS)
                    ->orderBy($criteria->sort->value, $criteria->order->value)
                    ->orderBy('venue_id'),
                $criteria->page,
                $criteria->perPage,
            ),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function maxPage(VenueSearchCriteria $criteria): int
    {
        return $this->queryFactory->maxPage($this->buildSearchQuery($criteria), $criteria->perPage);
    }

    #[Override]
    public function find(VenueId $venueId): ?Venue
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('venue_id', '=', $this->converter->toBin($venueId->value))
                ->limit(1),
        );

        $row = $rows[0] ?? null;

        return $row === null ? null : $this->hydrate($row);
    }

    #[Override]
    public function findByIds(VenueId ...$venueIds): array
    {
        if ($venueIds === []) {
            return [];
        }

        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('venue_id', 'IN', array_map(fn (VenueId $venueId): string => $this->converter->toBin($venueId->value), $venueIds)),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function isUsed(VenueId $venueId): bool
    {
        $count = Row::intValue(
            $this->queryFactory->select()
                ->from('event_venues')
                ->where('venue_id', '=', $this->converter->toBin($venueId->value))
                ->aggregate($this->queryFactory->pdo(), 'COUNT(*)'),
        );

        return $count > 0;
    }

    #[Override]
    public function save(Venue $venue): Venue
    {
        $now = now()->toDateTimeString();

        $this->queryFactory->insert()
            ->into(self::TABLE, ['venue_id', 'name', 'kind', 'created_at', 'updated_at'])
            ->values([
                $this->converter->toBin($venue->venueId->value),
                $venue->name->value,
                $venue->kind->value,
                $now,
                $now,
            ])
            ->build()
            ->append(
                'ON DUPLICATE KEY UPDATE '
                    . '`name` = VALUES(`name`), '
                    . '`kind` = VALUES(`kind`), '
                    . '`updated_at` = VALUES(`updated_at`)',
            )
            ->execute($this->queryFactory->pdo());

        return $venue;
    }

    #[Override]
    public function delete(VenueId $venueId): void
    {
        $this->queryFactory->delete()
            ->from(self::TABLE)
            ->where('venue_id', '=', $this->converter->toBin($venueId->value))
            ->execute($this->queryFactory->pdo());
    }

    private function buildSearchQuery(VenueSearchCriteria $criteria): SelectBuilder
    {
        $query = $this->queryFactory->select()->from(self::TABLE);

        if ($criteria->name->isPresent()) {
            $query = $query->where(
                'name_lower',
                'LIKE',
                SqlHelper::containsPattern(mb_strtolower($criteria->name->get())),
            );
        }

        if ($criteria->kind->isPresent()) {
            $query = $query->where('kind', '=', $criteria->kind->get()->value);
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Venue
    {
        return Venue::reconstruct(
            $this->converter->toUuid(Row::string($row, 'venue_id')),
            Row::string($row, 'name'),
            $this->toVenueKindValue(Row::string($row, 'kind')),
        );
    }

    private function toVenueKindValue(string $kind): int
    {
        return is_numeric($kind) ? (int)$kind : match ($kind) {
            'physical' => VenueKind::Physical->value,
            'online' => VenueKind::Online->value,
            default => throw new UnexpectedValueException('開催先種別が不正です。'),
        };
    }
}
