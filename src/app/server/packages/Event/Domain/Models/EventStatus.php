<?php

declare(strict_types=1);

namespace Event\Domain\Models;

enum EventStatus: int
{
    case Postponed = 1;

    case Cancelled = 2;
}
