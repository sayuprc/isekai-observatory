<?php

declare(strict_types=1);

namespace Auth\Domain\Services;

interface PasskeyUserHandleGeneratorInterface
{
    public function generate(): string;
}
