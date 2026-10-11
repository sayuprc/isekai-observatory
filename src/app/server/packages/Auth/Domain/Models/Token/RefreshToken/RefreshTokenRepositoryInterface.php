<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Token\RefreshToken;

interface RefreshTokenRepositoryInterface
{
    public function findActive(RefreshTokenId $refreshTokenId): ?RefreshToken;

    public function save(RefreshToken $refreshToken): RefreshToken;
}
