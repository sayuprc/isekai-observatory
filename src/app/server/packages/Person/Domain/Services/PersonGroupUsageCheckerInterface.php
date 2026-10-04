<?php

declare(strict_types=1);

namespace Person\Domain\Services;

use Person\Domain\Models\PersonGroupId;

interface PersonGroupUsageCheckerInterface
{
    public function isUsed(PersonGroupId $personGroupId): bool;
}
