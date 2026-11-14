<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Token\RefreshToken;

use SensitiveParameter;

interface RefreshTokenRepositoryInterface
{
    public function findActive(RefreshTokenId $refreshTokenId): ?RefreshToken;

    public function save(#[SensitiveParameter] RefreshToken $refreshToken): RefreshToken;
}
