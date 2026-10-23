<?php

declare(strict_types=1);

namespace Person\Application\Public\UseCase\Group\List;

use Person\Application\Public\Query\PersonGroupListItem;

readonly class ListOutputData
{
    /**
     * @param list<PersonGroupListItem> $personGroups
     */
    public function __construct(
        public array $personGroups,
        public ?string $nextPageToken,
    ) {
    }
}
