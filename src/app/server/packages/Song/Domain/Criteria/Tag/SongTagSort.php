<?php

declare(strict_types=1);

namespace Song\Domain\Criteria\Tag;

enum SongTagSort: string
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
