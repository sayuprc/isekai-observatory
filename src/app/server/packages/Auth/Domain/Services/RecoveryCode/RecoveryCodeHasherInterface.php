<?php

declare(strict_types=1);

namespace Auth\Domain\Services\RecoveryCode;

use SensitiveParameter;

interface RecoveryCodeHasherInterface
{
    public function hash(#[SensitiveParameter] string $plainCode): string;

    public function verify(#[SensitiveParameter] string $plainCode, string $hashedCode): bool;
}
