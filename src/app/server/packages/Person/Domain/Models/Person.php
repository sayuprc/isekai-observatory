<?php

declare(strict_types=1);

namespace Person\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

readonly class Person
{
    public function __construct(
        public PersonId $personId,
        public PersonName $name,
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(string $personId, string $name, int $orderNo): self
    {
        return new self(
            new PersonId($personId),
            new PersonName($name),
            new OrderNo($orderNo),
        );
    }

    /**
     * @return array{person_id: string, name: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'person_id' => $this->personId->value,
            'name' => $this->name->value,
            'order_no' => $this->orderNo->value,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->personId->equals($other->personId);
    }
}
