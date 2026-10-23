<?php

declare(strict_types=1);

namespace Person\Application\Public\UseCase\List;

use Person\Application\Public\Query\PersonListItem;

readonly class ListOutputData
{
    /**
     * @param list<PersonListItem> $persons
     */
    public function __construct(
        public array $persons,
        public ?string $nextPageToken,
    ) {
    }
}
