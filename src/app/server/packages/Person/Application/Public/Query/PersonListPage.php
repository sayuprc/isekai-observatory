<?php

declare(strict_types=1);

namespace Person\Application\Public\Query;

readonly class PersonListPage
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
