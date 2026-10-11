<?php

declare(strict_types=1);

namespace Song\Application\Admin\Assemble;

use Song\Domain\Models\Persons\SongPersonRole;

readonly class AssembledPerson
{
    public function __construct(
        public string $personId,
        public string $name,
        public SongPersonRole $role,
        public int $orderNo,
    ) {
    }
}
