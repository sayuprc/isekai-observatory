<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models\RegistrationToken;

use Support\Domain\ValueObjects\String\StringValueObject;

readonly class HashedTokenValue extends StringValueObject
{
}
