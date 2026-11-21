<?php

declare(strict_types=1);

namespace Public\Application\Release\UseCase\List;

use Public\Application\Release\UseCase\List\Query\ReleaseGroupListItem;
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
