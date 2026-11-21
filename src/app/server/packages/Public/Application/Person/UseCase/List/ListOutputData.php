<?php

declare(strict_types=1);

namespace Public\Application\Person\UseCase\List;

use Public\Application\Person\UseCase\List\Query\PersonListItem;
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
