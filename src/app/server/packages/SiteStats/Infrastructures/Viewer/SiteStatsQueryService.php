<?php

declare(strict_types=1);

namespace SiteStats\Infrastructures\Viewer;

use Override;
use SiteStats\Application\Viewer\Query\SiteStats;
use SiteStats\Application\Viewer\Query\SiteStatsQueryServiceInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class SiteStatsQueryService implements SiteStatsQueryServiceInterface
{
    public function __construct(private QueryFactory $queryFactory)
    {
    }

    #[Override]
    public function get(): SiteStats
    {
        $songCount = Row::intValue(
            $this->queryFactory->select()
                ->from('songs')
                ->where('is_display', '=', true)
                ->aggregate($this->queryFactory->pdo(), 'COUNT(*)'),
        );

        // 公開リリースを 1 件以上持つ公開リリースグループのみを数える
        $releaseCount = Row::intValue(
            $this->queryFactory->select()
                ->from('release_groups')
                ->join('releases', 'releases.release_group_id = release_groups.release_group_id AND releases.is_display = TRUE')
                ->where('release_groups.is_display', '=', true)
                ->aggregate($this->queryFactory->pdo(), 'COUNT(DISTINCT release_groups.release_group_id)'),
        );

        return new SiteStats($songCount, $releaseCount);
    }
}
