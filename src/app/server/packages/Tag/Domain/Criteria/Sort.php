<?php

declare(strict_types=1);

namespace Tag\Domain\Criteria;

enum Sort: string
{
    case Name = 'name';

    case OrderNo = 'order_no';

    public function isName(): bool
    {
        return $this === self::Name;
    }

    public function isOrderNo(): bool
    {
        return $this === self::OrderNo;
    }
}
