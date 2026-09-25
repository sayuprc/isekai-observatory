<?php

declare(strict_types=1);

namespace Event\Domain\Models\Performances;

use Person\Domain\Models\PersonId;
use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, CoVocalist>
 *
 * @phpstan-type _coVocalistInput array{personId: string, creditName: ?string, orderNo: int}
 */
readonly class CoVocalists extends ImmutableCollection
{
    /**
     * @param list<_coVocalistInput> $items
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(array $items): self
    {
        if (count($items) !== count(array_unique(array_column($items, 'personId')))) {
            throw new BusinessRuleViolationException('楽曲披露の共演者を重複して登録できません');
        }

        if (count($items) !== count(array_unique(array_column($items, 'orderNo')))) {
            throw new BusinessRuleViolationException('楽曲披露の共演者順序を重複して登録できません');
        }

        return self::reconstruct($items);
    }

    /**
     * @param list<_coVocalistInput> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): CoVocalist => new CoVocalist(
                new PersonId($item['personId']),
                $item['creditName'] === null ? null : new CreditName($item['creditName']),
                new OrderNo($item['orderNo']),
            ),
            $items,
        ));
    }

    /**
     * @return list<array{person_id: string, credit_name: ?string, order_no: int}>
     */
    public function toArray(): array
    {
        return array_values(array_map(static fn (CoVocalist $coVocalist): array => $coVocalist->toArray(), $this->items));
    }
}
