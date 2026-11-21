<?php

declare(strict_types=1);

namespace Public\Application\Release\UseCase\List\Query;

use SensitiveParameter;

readonly class ReleaseGroupListPage
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
