<?php

declare(strict_types=1);

namespace Person\Application\Public\Query;

use SensitiveParameter;

readonly class PersonGroupListPage
{
    /**
     * @param list<PersonGroupListItem> $personGroups
     */
    public function __construct(
        public array $personGroups,
        #[SensitiveParameter]
        public ?string $nextPageToken,
    ) {
    }
}
