<?php

declare(strict_types=1);

namespace Person\Domain\Models;

/**
 * 共演者をまとめて入力・表示するための人物グループ
 * 所属期間は持たず、楽曲披露の記録は人単位で残す
 */
readonly class PersonGroup
{
    public function __construct(
        public PersonGroupId $personGroupId,
        public PersonGroupName $name,
        public PersonGroupMembers $members,
    ) {
    }

    /**
     * @return array{person_group_id: string, name: string, members: list<array{person_id: string, order_no: int}>}
     */
    public function toArray(): array
    {
        return [
            'person_group_id' => $this->personGroupId->value,
            'name' => $this->name->value,
            'members' => $this->members->toArray(),
        ];
    }

    public function equals(self $other): bool
    {
        return $this->personGroupId->equals($other->personGroupId);
    }
}
