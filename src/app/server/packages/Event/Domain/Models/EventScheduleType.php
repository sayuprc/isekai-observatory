<?php

declare(strict_types=1);

namespace Event\Domain\Models;

enum EventScheduleType: int
{
    case Undated = 1;

    case Date = 2;

    case DateRange = 3;

    case DateTime = 4;
}
