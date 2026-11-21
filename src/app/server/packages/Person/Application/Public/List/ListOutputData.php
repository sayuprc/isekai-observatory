<?php

declare(strict_types=1);

namespace Person\Application\Public\List;

use Person\Application\Public\List\Query\PersonListItem;
use SensitiveParameter;

readonly class ListOutputData
{
    /**
     * @param list<PersonListItem> $persons
     */
    public function __construct(
        public array $persons,
        #[SensitiveParameter]
        public ?string $nextPageToken,
    ) {
    }
}
