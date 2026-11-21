<?php

declare(strict_types=1);

namespace Public\Application\Person\UseCase\List\Query;

use SensitiveParameter;

readonly class PersonListPage
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
