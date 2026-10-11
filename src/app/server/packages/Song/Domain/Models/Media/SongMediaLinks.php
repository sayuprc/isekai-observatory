<?php

declare(strict_types=1);

namespace Song\Domain\Models\Media;

use Media\Domain\Models\MediaId;
use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, SongMediaLink>
 */
readonly class SongMediaLinks extends ImmutableCollection
{
    /**
     * @param list<array{mediaId: string, orderNo: int}> $items
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(array $items): self
    {
        $links = [];
        $seen = [];

        foreach ($items as $item) {
            $link = new SongMediaLink(new MediaId($item['mediaId']), new OrderNo($item['orderNo']));

            if (isset($seen[$link->mediaId->value])) {
                throw new BusinessRuleViolationException('同じメディアを複数指定することはできません。');
            }

            $seen[$link->mediaId->value] = true;
            $links[] = $link;
        }

        return new self($links);
    }

    /**
     * @param list<array{mediaId: string, orderNo: int}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): SongMediaLink => SongMediaLink::reconstruct(
                $item['mediaId'],
                $item['orderNo'],
            ),
            $items,
        ));
    }

    /**
     * @return list<array{media_id: string, order_no: int}>
     */
    public function toArray(): array
    {
        $items = [];

        foreach ($this->toGeneric() as $item) {
            $items[] = $item->toArray();
        }

        return $items;
    }
}
