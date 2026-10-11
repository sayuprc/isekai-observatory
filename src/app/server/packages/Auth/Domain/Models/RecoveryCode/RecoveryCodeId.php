<?php

declare(strict_types=1);

namespace Auth\Domain\Models\RecoveryCode;

use Support\Domain\ValueObjects\String\UuidValueObject;

readonly class RecoveryCodeId extends UuidValueObject
{
}
