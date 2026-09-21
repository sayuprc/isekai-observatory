<?php

declare(strict_types=1);

namespace Auth\Domain\Services\RecoveryCode;

interface RandomRecoveryCodeGeneratorInterface
{
    public function generate(): string;
}
