<?php

declare(strict_types=1);

namespace Person\Infrastructures\Admin;

use Override;
use Person\Application\Admin\Query\PersonGroupUsageCountQueryServiceInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;

readonly class PersonGroupUsageCountQueryService implements PersonGroupUsageCountQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function countPerformances(array $personGroupIds): array
    {
        // 件数は渡された ID でまとめて引き、1 件ごとの問い合わせを避ける
        $binIds = array_map($this->converter->toBin(...), $personGroupIds);
        $counts = $this->queryFactory->countBy('song_performance_persons', 'person_group_id', $binIds, 'performance_id');

        $result = [];
        foreach ($binIds as $binId) {
            $result[$this->converter->toUuid($binId)] = $counts[$binId] ?? 0;
        }

        return $result;
    }
}
