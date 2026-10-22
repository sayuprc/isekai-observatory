<?php

declare(strict_types=1);

namespace Person\Application\Public\Query;

readonly class PersonListItem
{
    public function __construct(
        public string $personId,
        public string $name,
    ) {
    }
}
