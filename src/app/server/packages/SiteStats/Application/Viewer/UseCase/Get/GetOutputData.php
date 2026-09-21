<?php

declare(strict_types=1);

namespace SiteStats\Application\Viewer\UseCase\Get;

readonly class GetOutputData
{
    public function __construct(
        public int $songCount,
        public int $releaseCount,
    ) {
    }
}
