<?php

declare(strict_types=1);

namespace Event\Domain\Models\Media;

use Media\Domain\Models\MediaId;
use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, EventMediaLink>
 */
readonly class EventMediaLinks extends ImmutableCollection
{
    /**
     * @param list<array{mediaId: string, orderNo: int}> $items
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(array $items): self
    {
        if (count($items) !== count(array_unique(array_column($items, 'mediaId')))) {
            throw new BusinessRuleViolationException('メディアを重複して登録できません');
        }

        if (count($items) !== count(array_unique(array_column($items, 'orderNo')))) {
            throw new BusinessRuleViolationException('メディアの順序を重複して登録できません');
        }

        return self::reconstruct($items);
    }

    /**
     * @param list<array{mediaId: string, orderNo: int}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): EventMediaLink => new EventMediaLink(new MediaId($item['mediaId']), new OrderNo($item['orderNo'])),
            $items,
        ));
    }

    /**
     * @return list<array{media_id: string, order_no: int}>
     */
    public function toArray(): array
    {
        return array_values(array_map(static fn (EventMediaLink $link): array => $link->toArray(), $this->items));
    }
}
