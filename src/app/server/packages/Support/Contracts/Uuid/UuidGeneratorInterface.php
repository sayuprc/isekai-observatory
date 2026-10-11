<?php

declare(strict_types=1);

namespace Support\Contracts\Uuid;

interface UuidGeneratorInterface
{
    public function generate(): string;
}
