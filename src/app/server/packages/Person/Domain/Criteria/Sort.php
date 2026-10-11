<?php

declare(strict_types=1);

namespace Person\Domain\Criteria;

enum Sort: string
{
    case Name = 'name';

    case OrderNo = 'order_no';
}
