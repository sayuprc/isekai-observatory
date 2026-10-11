<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Token\RefreshToken;

use Support\Domain\ValueObjects\String\StringValueObject;

readonly class HashedTokenValue extends StringValueObject
{
}
