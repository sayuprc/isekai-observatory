<?php

declare(strict_types=1);

namespace Release\Application\Public\UseCase\List;

use Release\Application\Public\Query\ReleaseGroupListItem;
use SensitiveParameter;

readonly class ListOutputData
{
    /**
     * @param list<ReleaseGroupListItem> $releaseGroups
     */
    public function __construct(
        public array $releaseGroups,
        #[SensitiveParameter]
        public ?string $nextPageToken,
    ) {
    }
}
