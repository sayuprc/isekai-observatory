<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Token\RefreshToken;

use Support\Domain\ValueObjects\String\UuidValueObject;

readonly class RefreshTokenId extends UuidValueObject
{
}
