<?php

declare(strict_types=1);

namespace Public\Application\Person\UseCase\Group\List\Query;

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
