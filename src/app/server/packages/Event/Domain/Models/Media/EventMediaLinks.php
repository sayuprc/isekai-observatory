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
     * 並び順は指定順で採番する
     *
     * @param list<string> $mediaIds
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(array $mediaIds): self
    {
        if (count($mediaIds) !== count(array_unique($mediaIds))) {
            throw new BusinessRuleViolationException('メディアを重複して登録できません');
        }

        $links = [];
        foreach ($mediaIds as $index => $mediaId) {
            $links[] = new EventMediaLink(new MediaId($mediaId), new OrderNo($index + 1));
        }

        return new self($links);
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
