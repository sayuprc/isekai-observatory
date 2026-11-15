<?php

declare(strict_types=1);

namespace Release\Infrastructures\Admin;

use Override;
use Release\Application\Admin\Query\ReleaseGroupDetailQueryServiceInterface;
use Release\Application\Admin\Query\ReleaseGroupReferencedRelease;
use Release\Domain\Models\ReleaseGroupId;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class ReleaseGroupDetailQueryService implements ReleaseGroupDetailQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function findReferencedReleases(ReleaseGroupId $releaseGroupId): array
    {
        $binGroupId = $this->converter->toBin($releaseGroupId->value);

        return $this->findReferencedReleasesByGroup([$binGroupId])[$binGroupId] ?? [];
    }

    /**
     * 複数グループの傘下リリースをまとめて引く。リリースのないグループは結果に含まれない
     *
     * @param array<int, string> $binGroupIds
     *
     * @return array<string, list<ReleaseGroupReferencedRelease>>
     */
    public function findReferencedReleasesByGroup(array $binGroupIds): array
    {
        if ($binGroupIds === []) {
            return [];
        }

        $releaseRows = $this->queryFactory
            ->select()
            ->withSelect(['release_id', 'release_group_id', 'name', 'released_on', 'is_display', 'order_no'])
            ->from('releases')
            ->where('release_group_id', 'IN', $binGroupIds)
            ->orderBy('released_on')
            ->orderBy('order_no')
            ->orderBy('name')
            |> $this->queryFactory->fetchAll(...);

        if ($releaseRows === []) {
            return [];
        }

        $binReleaseIds = array_map(static fn (array $row): string => Row::string($row, 'release_id'), $releaseRows);

        $formatRows = $this->queryFactory
            ->select()
            ->withSelect(['release_id', 'format'])
            ->from('release_formats')
            ->where('release_id', 'IN', $binReleaseIds)
            ->orderBy('format')
            |> $this->queryFactory->fetchAll(...);

        $formatsByRelease = [];

        foreach ($formatRows as $formatRow) {
            $formatsByRelease[Row::string($formatRow, 'release_id')][] = Row::int($formatRow, 'format');
        }

        $releasesByGroup = [];

        foreach ($releaseRows as $row) {
            $releasesByGroup[Row::string($row, 'release_group_id')][] = new ReleaseGroupReferencedRelease(
                $this->converter->toUuid(Row::string($row, 'release_id')),
                Row::string($row, 'name'),
                Row::string($row, 'released_on'),
                Row::bool($row, 'is_display'),
                Row::int($row, 'order_no'),
                $formatsByRelease[Row::string($row, 'release_id')] ?? [],
            );
        }

        return $releasesByGroup;
    }
}
