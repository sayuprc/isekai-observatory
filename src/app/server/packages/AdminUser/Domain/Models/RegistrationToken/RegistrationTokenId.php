<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models\RegistrationToken;

use Support\Domain\ValueObjects\String\UuidValueObject;

readonly class RegistrationTokenId extends UuidValueObject
{
}
