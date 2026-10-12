<?php

declare(strict_types=1);

namespace Person\Domain\Models;

/**
 * 共演者をまとめて入力・表示するための人物グループ
 * 所属期間は持たず、楽曲披露の記録は人単位で残す
 * 一時離脱は離脱パターンごとに別グループで表すため、管理用名称は一意にして表示用名称は重複を許す
 */
readonly class PersonGroup
{
    public function __construct(
        public PersonGroupId $personGroupId,
        public PersonGroupName $name,
        public PersonGroupDisplayName $displayName,
        public PersonGroupMembers $members,
    ) {
    }

    /**
     * @return array{person_group_id: string, name: string, display_name: string, members: list<array{person_id: string, order_no: int}>}
     */
    public function toArray(): array
    {
        return [
            'person_group_id' => $this->personGroupId->value,
            'name' => $this->name->value,
            'display_name' => $this->displayName->value,
            'members' => $this->members->toArray(),
        ];
    }

    public function equals(self $other): bool
    {
        return $this->personGroupId->equals($other->personGroupId);
    }
}
