<?php

declare(strict_types=1);

namespace Public\Application\Person\UseCase\Group\List;

use Public\Application\Person\UseCase\Group\List\Query\PersonGroupListItem;
use SensitiveParameter;

readonly class ListOutputData
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
