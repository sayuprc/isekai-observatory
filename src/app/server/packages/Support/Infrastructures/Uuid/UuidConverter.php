<?php

declare(strict_types=1);

namespace Support\Infrastructures\Uuid;

use Override;
use Ramsey\Uuid\Uuid;
use Support\Contracts\Uuid\UuidConverterInterface;

class UuidConverter implements UuidConverterInterface
{
    #[Override]
    public function toBin(string $uuid): string
    {
        return Uuid::fromString($uuid)->getBytes();
    }

    #[Override]
    public function toUuid(string $bin): string
    {
        return Uuid::fromBytes($bin)->toString();
    }
}
