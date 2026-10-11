<?php

declare(strict_types=1);

namespace Song\Domain\Criteria;

enum Sort: string
{
    case Title = 'title';

    case OrderNo = 'order_no';

    public function isTitle(): bool
    {
        return $this === self::Title;
    }

    public function isOrderNo(): bool
    {
        return $this === self::OrderNo;
    }
}
