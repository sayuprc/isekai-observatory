<?php

declare(strict_types=1);

namespace Support\Infrastructures;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Override;
use Support\Contracts\ClockInterface;

readonly class Clock implements ClockInterface
{
    #[Override]
    public function now(): DateTimeImmutable
    {
        return new CarbonImmutable();
    }
}
