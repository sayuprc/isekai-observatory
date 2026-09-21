<?php

declare(strict_types=1);

namespace Person\Domain\Services;

use Person\Domain\Models\PersonId;

interface PersonUsageCheckerInterface
{
    public function isUsed(PersonId $personId): bool;
}
