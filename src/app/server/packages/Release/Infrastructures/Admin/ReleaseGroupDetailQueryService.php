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
        $releaseRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['release_id', 'name', 'released_on', 'color', 'is_display', 'order_no'])
                ->from('releases')
                ->where('release_group_id', '=', $this->converter->toBin($releaseGroupId->value))
                ->orderBy('released_on')
                ->orderBy('order_no')
                ->orderBy('name'),
        );

        if ($releaseRows === []) {
            return [];
        }

        $binReleaseIds = array_map(static fn (array $row): string => Row::string($row, 'release_id'), $releaseRows);

        $formatRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['release_id', 'format'])
                ->from('release_formats')
                ->where('release_id', 'IN', $binReleaseIds)
                ->orderBy('format'),
        );

        $formatsByRelease = [];

        foreach ($formatRows as $formatRow) {
            $formatsByRelease[Row::string($formatRow, 'release_id')][] = Row::int($formatRow, 'format');
        }

        return array_map(
            fn (array $row): ReleaseGroupReferencedRelease => new ReleaseGroupReferencedRelease(
                $this->converter->toUuid(Row::string($row, 'release_id')),
                Row::string($row, 'name'),
                Row::string($row, 'released_on'),
                Row::string($row, 'color'),
                Row::bool($row, 'is_display'),
                Row::int($row, 'order_no'),
                $formatsByRelease[Row::string($row, 'release_id')] ?? [],
            ),
            $releaseRows,
        );
    }
}
