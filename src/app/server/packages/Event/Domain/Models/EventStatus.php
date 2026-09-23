<?php

declare(strict_types=1);

namespace Event\Domain\Models;

enum EventStatus: int
{
    case Normal = 0;

    case Postponed = 1;

    case Cancelled = 2;
}
