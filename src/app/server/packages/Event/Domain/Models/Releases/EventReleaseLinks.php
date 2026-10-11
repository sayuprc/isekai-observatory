<?php

declare(strict_types=1);

namespace Event\Domain\Models\Releases;

use Release\Domain\Models\ReleaseId;
use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, EventReleaseLink>
 */
readonly class EventReleaseLinks extends ImmutableCollection
{
    /**
     * @param list<array{releaseId: string, orderNo: int}> $items
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(array $items): self
    {
        if (count($items) !== count(array_unique(array_column($items, 'releaseId')))) {
            throw new BusinessRuleViolationException('リリースを重複して登録できません');
        }

        if (count($items) !== count(array_unique(array_column($items, 'orderNo')))) {
            throw new BusinessRuleViolationException('リリースの順序を重複して登録できません');
        }

        return self::reconstruct($items);
    }

    /**
     * @param list<array{releaseId: string, orderNo: int}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): EventReleaseLink => new EventReleaseLink(new ReleaseId($item['releaseId']), new OrderNo($item['orderNo'])),
            $items,
        ));
    }

    /**
     * @return list<array{release_id: string, order_no: int}>
     */
    public function toArray(): array
    {
        return array_values(array_map(static fn (EventReleaseLink $link): array => $link->toArray(), $this->items));
    }
}
