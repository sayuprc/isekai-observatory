<?php

declare(strict_types=1);

namespace Release\Application\Viewer\UseCase\List;

use Release\Application\Viewer\Query\ReleaseGroupListItem;

readonly class ListOutputData
{
    /**
     * @param array<ReleaseGroupListItem> $releaseGroups
     */
    public function __construct(
        public array $releaseGroups,
        public ?string $nextCursor,
    ) {
    }
}
