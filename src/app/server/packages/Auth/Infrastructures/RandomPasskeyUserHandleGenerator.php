<?php

declare(strict_types=1);

namespace Auth\Infrastructures;

use Auth\Domain\Services\PasskeyUserHandleGeneratorInterface;
use Override;

readonly class RandomPasskeyUserHandleGenerator implements PasskeyUserHandleGeneratorInterface
{
    private const int LENGTH = 64;

    #[Override]
    public function generate(): string
    {
        return random_bytes(self::LENGTH);
    }
}
