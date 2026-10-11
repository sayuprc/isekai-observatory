<?php

declare(strict_types=1);

namespace Support\Contracts;

use DateTimeImmutable;

interface ClockInterface
{
    public function now(): DateTimeImmutable;
}
