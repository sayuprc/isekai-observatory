<?php

declare(strict_types=1);

namespace Release\Domain\Criteria;

enum Sort: string
{
    case FirstReleasedOn = 'first_released_on';

    case Title = 'title';
}
