<?php

declare(strict_types=1);

namespace SiteStats\Application\Viewer\Query;

interface SiteStatsQueryServiceInterface
{
    public function get(): SiteStats;
}
