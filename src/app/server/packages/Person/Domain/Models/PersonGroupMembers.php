<?php

declare(strict_types=1);

namespace Person\Domain\Models;

use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, PersonGroupMember>
 *
 * @phpstan-type _memberInput array{personId: string, orderNo: int}
 */
readonly class PersonGroupMembers extends ImmutableCollection
{
    /**
     * @param list<_memberInput> $items
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(array $items): self
    {
        if (count($items) !== count(array_unique(array_column($items, 'personId')))) {
            throw new BusinessRuleViolationException('人物グループのメンバーを重複して登録できません');
        }

        if (count($items) !== count(array_unique(array_column($items, 'orderNo')))) {
            throw new BusinessRuleViolationException('人物グループのメンバー順序を重複して登録できません');
        }

        return self::reconstruct($items);
    }

    /**
     * @param list<_memberInput> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): PersonGroupMember => new PersonGroupMember(
                new PersonId($item['personId']),
                new OrderNo($item['orderNo']),
            ),
            $items,
        ));
    }

    /**
     * @return list<PersonId>
     */
    public function personIds(): array
    {
        return array_values(array_map(static fn (PersonGroupMember $member): PersonId => $member->personId, $this->items));
    }

    /**
     * @return list<array{person_id: string, order_no: int}>
     */
    public function toArray(): array
    {
        return array_values(array_map(static fn (PersonGroupMember $member): array => $member->toArray(), $this->items));
    }
}
