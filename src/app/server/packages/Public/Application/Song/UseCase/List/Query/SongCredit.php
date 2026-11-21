<?php

declare(strict_types=1);

namespace Public\Application\Song\UseCase\List\Query;

use Song\Domain\Models\Persons\SongPersonRole;

readonly class SongCredit
{
    public function __construct(
        public string $personId,
        public SongPersonRole $role,
    ) {
    }
}
