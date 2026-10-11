<?php

declare(strict_types=1);

namespace Support\Domain\SearchCriteria;

enum PerPage: int
{
    case TwentyFive = 25;

    case Fifty = 50;

    case Hundred = 100;
}
