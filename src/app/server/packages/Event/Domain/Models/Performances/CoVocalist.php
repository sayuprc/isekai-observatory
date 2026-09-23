<?php

declare(strict_types=1);

namespace Event\Domain\Models\Performances;

use Person\Domain\Models\PersonId;
use Support\Domain\ValueObjects\OrderNo;

readonly class CoVocalist
{
    public function __construct(
        public PersonId $personId,
        public ?CreditName $creditName,
        public OrderNo $orderNo,
    ) {
    }

    /**
     * @return array{person_id: string, credit_name: ?string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'person_id' => $this->personId->value,
            'credit_name' => $this->creditName?->value,
            'order_no' => $this->orderNo->value,
        ];
    }
}
