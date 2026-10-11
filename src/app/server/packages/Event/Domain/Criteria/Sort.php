<?php

declare(strict_types=1);

namespace Event\Domain\Criteria;

enum Sort: string
{
    case Schedule = 'schedule';

    case Title = 'title';
}
