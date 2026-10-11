<?php

declare(strict_types=1);

namespace Venue\Infrastructures\Admin;

use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Venue\Application\Admin\Query\VenueUsageCountQueryServiceInterface;
use Venue\Domain\Models\VenueId;

readonly class VenueUsageCountQueryService implements VenueUsageCountQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function countEvents(array $venueIds): array
    {
        // 件数は渡された ID でまとめて引き、1 件ごとの問い合わせを避ける
        $binIds = array_map(fn (VenueId $id): string => $this->converter->toBin($id->value), $venueIds);
        $counts = $this->queryFactory->countBy('event_venues', 'venue_id', $binIds);

        $result = [];
        foreach ($binIds as $binId) {
            $result[$this->converter->toUuid($binId)] = $counts[$binId] ?? 0;
        }

        return $result;
    }
}
