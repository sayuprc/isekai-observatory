<?php

declare(strict_types=1);

namespace Auth\Domain\Services\Token\RefreshToken;

interface RandomTokenGeneratorInterface
{
    public function generate(): string;
}
