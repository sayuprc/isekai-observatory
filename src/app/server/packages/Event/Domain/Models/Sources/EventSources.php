<?php

declare(strict_types=1);

namespace Event\Domain\Models\Sources;

use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, EventSource>
 */
readonly class EventSources extends ImmutableCollection
{
    /**
     * @param list<array{displayName: string, url: string, orderNo: int}> $items
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(array $items): self
    {
        $sources = self::reconstruct($items);

        if (count($items) !== count(array_unique(array_column($items, 'url')))) {
            throw new BusinessRuleViolationException('出典URLを重複して登録できません');
        }

        if (count($items) !== count(array_unique(array_column($items, 'orderNo')))) {
            throw new BusinessRuleViolationException('出典の順序を重複して登録できません');
        }

        return $sources;
    }

    /**
     * @param list<array{displayName: string, url: string, orderNo: int}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): EventSource => new EventSource(
                new EventSourceName($item['displayName']),
                new EventSourceUrl($item['url']),
                new OrderNo($item['orderNo']),
            ),
            $items,
        ));
    }

    /**
     * @return list<array{name: string, url: string, order_no: int}>
     */
    public function toArray(): array
    {
        return array_values(array_map(static fn (EventSource $source): array => $source->toArray(), $this->items));
    }
}
