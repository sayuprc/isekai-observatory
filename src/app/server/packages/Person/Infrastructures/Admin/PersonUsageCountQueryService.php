<?php

declare(strict_types=1);

namespace Person\Infrastructures\Admin;

use Override;
use Person\Application\Admin\Query\PersonUsageCount;
use Person\Application\Admin\Query\PersonUsageCountQueryServiceInterface;
use Person\Domain\Models\PersonId;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;

readonly class PersonUsageCountQueryService implements PersonUsageCountQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function countUsages(array $personIds): array
    {
        // 件数は渡された ID でまとめて引き、人物ごとの問い合わせを避ける
        // 1 曲で作詞と作曲を兼ねる人物もいるので、楽曲は重複なく数える
        $binPersonIds = array_map(fn (PersonId $personId): string => $this->converter->toBin($personId->value), $personIds);
        $songCounts = $this->queryFactory->countBy('song_persons', 'person_id', $binPersonIds, 'song_id');
        $performanceCounts = $this->queryFactory->countBy('song_performance_persons', 'person_id', $binPersonIds, 'performance_id');

        $usages = [];
        foreach ($binPersonIds as $binPersonId) {
            $usages[$this->converter->toUuid($binPersonId)] = new PersonUsageCount(
                $songCounts[$binPersonId] ?? 0,
                $performanceCounts[$binPersonId] ?? 0,
            );
        }

        return $usages;
    }
}
