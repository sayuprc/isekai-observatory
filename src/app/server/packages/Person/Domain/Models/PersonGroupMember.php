<?php

declare(strict_types=1);

namespace Person\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

readonly class PersonGroupMember
{
    public function __construct(
        public PersonId $personId,
        public OrderNo $orderNo,
    ) {
    }

    /**
     * @return array{person_id: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'person_id' => $this->personId->value,
            'order_no' => $this->orderNo->value,
        ];
    }
}
