<?php

declare(strict_types=1);

namespace AdminUser\Domain\Services\RegistrationToken;

interface RandomTokenGeneratorInterface
{
    public function generate(): string;
}
