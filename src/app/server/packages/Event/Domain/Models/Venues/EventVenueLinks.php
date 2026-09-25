<?php

declare(strict_types=1);

namespace Event\Domain\Models\Venues;

use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\ValueObjects\OrderNo;
use Venue\Domain\Models\VenueId;

/**
 * @extends ImmutableCollection<int, EventVenueLink>
 */
readonly class EventVenueLinks extends ImmutableCollection
{
    /**
     * @param list<array{venueId: string, orderNo: int}> $items
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(array $items): self
    {
        if (count($items) !== count(array_unique(array_column($items, 'venueId')))) {
            throw new BusinessRuleViolationException('開催先を重複して登録できません');
        }

        if (count($items) !== count(array_unique(array_column($items, 'orderNo')))) {
            throw new BusinessRuleViolationException('開催先の順序を重複して登録できません');
        }

        return self::reconstruct($items);
    }

    /**
     * @param list<array{venueId: string, orderNo: int}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): EventVenueLink => new EventVenueLink(new VenueId($item['venueId']), new OrderNo($item['orderNo'])),
            $items,
        ));
    }

    /**
     * @return list<array{venue_id: string, order_no: int}>
     */
    public function toArray(): array
    {
        return array_values(array_map(static fn (EventVenueLink $link): array => $link->toArray(), $this->items));
    }
}
