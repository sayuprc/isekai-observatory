<?php

declare(strict_types=1);

namespace Person\Application\Public\Query;

readonly class PersonGroupListPage
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
