<?php

declare(strict_types=1);

namespace Song\Domain\Models\Persons;

use Person\Domain\Models\PersonId;
use Support\Domain\ValueObjects\OrderNo;

readonly class SongPerson
{
    public function __construct(
        public PersonId $personId,
        public SongPersonRole $role,
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(string $personId, int $role, int $orderNo): self
    {
        return new self(
            new PersonId($personId),
            SongPersonRole::from($role),
            new OrderNo($orderNo),
        );
    }

    /**
     * @return array{person_id: string, role: value-of<SongPersonRole>, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'person_id' => $this->personId->value,
            'role' => $this->role->value,
            'order_no' => $this->orderNo->value,
        ];
    }
}
