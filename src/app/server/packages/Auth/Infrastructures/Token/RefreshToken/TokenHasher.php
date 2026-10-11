<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Token\RefreshToken;

use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use Illuminate\Support\Facades\Hash;
use Override;
use SensitiveParameter;

readonly class TokenHasher implements TokenHasherInterface
{
    #[Override]
    public function hash(#[SensitiveParameter] string $plainToken): string
    {
        return Hash::make($plainToken);
    }

    #[Override]
    public function verify(#[SensitiveParameter] string $plainToken, string $hashedToken): bool
    {
        return Hash::check($plainToken, $hashedToken);
    }
}
