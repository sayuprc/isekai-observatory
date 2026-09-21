<?php

declare(strict_types=1);

namespace SiteStats\Application\Viewer\UseCase\Get;

use SiteStats\Application\Viewer\Query\SiteStatsQueryServiceInterface;

readonly class GetUseCase
{
    public function __construct(private SiteStatsQueryServiceInterface $query)
    {
    }

    public function handle(): GetOutputData
    {
        $siteStats = $this->query->get();

        return new GetOutputData($siteStats->songCount, $siteStats->releaseCount);
    }
}
