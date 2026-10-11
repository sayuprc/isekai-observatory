<?php

declare(strict_types=1);

namespace Support\Domain\SearchCriteria;

enum Order: string
{
    case Asc = 'asc';

    case Desc = 'desc';

    public function isAsc(): bool
    {
        return $this === self::Asc;
    }

    public function isDesc(): bool
    {
        return $this === self::Desc;
    }
}
