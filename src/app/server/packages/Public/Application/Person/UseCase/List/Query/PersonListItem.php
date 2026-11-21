<?php

declare(strict_types=1);

namespace Public\Application\Person\UseCase\List\Query;

readonly class PersonListItem
{
    public function __construct(
        public string $personId,
        public string $name,
    ) {
    }
}
