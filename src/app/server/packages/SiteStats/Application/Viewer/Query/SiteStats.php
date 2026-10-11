<?php

declare(strict_types=1);

namespace SiteStats\Application\Viewer\Query;

readonly class SiteStats
{
    public function __construct(
        public int $songCount,
        public int $releaseCount,
    ) {
    }
}
