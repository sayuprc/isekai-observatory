<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Token\RefreshToken;

use Auth\Domain\Services\Token\RefreshToken\RandomTokenGeneratorInterface;
use Override;

readonly class RandomTokenGenerator implements RandomTokenGeneratorInterface
{
    private const int LENGTH = 64;

    #[Override]
    public function generate(): string
    {
        return bin2hex(random_bytes(self::LENGTH));
    }
}
