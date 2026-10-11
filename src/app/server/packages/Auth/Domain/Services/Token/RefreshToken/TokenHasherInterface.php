<?php

declare(strict_types=1);

namespace Auth\Domain\Services\Token\RefreshToken;

use SensitiveParameter;

interface TokenHasherInterface
{
    public function hash(#[SensitiveParameter] string $plainToken): string;

    public function verify(#[SensitiveParameter] string $plainToken, string $hashedToken): bool;
}
