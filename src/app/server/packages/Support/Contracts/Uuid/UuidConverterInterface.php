<?php

declare(strict_types=1);

namespace Support\Contracts\Uuid;

interface UuidConverterInterface
{
    public function toBin(string $uuid): string;

    public function toUuid(string $bin): string;
}
