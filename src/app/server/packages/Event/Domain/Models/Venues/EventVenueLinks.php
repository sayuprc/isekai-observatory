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
     * 並び順は指定順で採番する
     *
     * @param list<string> $venueIds
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(array $venueIds): self
    {
        if (count($venueIds) !== count(array_unique($venueIds))) {
            throw new BusinessRuleViolationException('開催先を重複して登録できません');
        }

        $links = [];
        foreach ($venueIds as $index => $venueId) {
            $links[] = new EventVenueLink(new VenueId($venueId), new OrderNo($index + 1));
        }

        return new self($links);
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
