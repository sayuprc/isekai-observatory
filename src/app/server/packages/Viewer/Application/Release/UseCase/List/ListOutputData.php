<?php

declare(strict_types=1);

namespace Viewer\Application\Release\UseCase\List;

use Viewer\Application\Release\UseCase\List\Query\ReleaseGroupListItem;

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
