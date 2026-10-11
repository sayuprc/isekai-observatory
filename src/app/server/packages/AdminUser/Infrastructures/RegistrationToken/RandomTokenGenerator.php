<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures\RegistrationToken;

use AdminUser\Domain\Services\RegistrationToken\RandomTokenGeneratorInterface;
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
