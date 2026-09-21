<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures\RegistrationToken;

use AdminUser\Domain\Services\RegistrationToken\TokenHasherInterface;
use Override;
use SensitiveParameter;

readonly class TokenHasher implements TokenHasherInterface
{
    #[Override]
    public function hash(#[SensitiveParameter] string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    #[Override]
    public function verify(#[SensitiveParameter] string $plainToken, string $hashedToken): bool
    {
        return hash_equals($this->hash($plainToken), $hashedToken);
    }
}
