<?php

declare(strict_types=1);

namespace Song\Domain\Models\Tag;

use Support\Domain\ValueObjects\OrderNo;

readonly class SongTag
{
    public function __construct(
        public SongTagId $songTagId,
        public SongTagName $name,
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(string $songTagId, string $name, int $orderNo): self
    {
        return new self(
            new SongTagId($songTagId),
            new SongTagName($name),
            new OrderNo($orderNo),
        );
    }

    /**
     * @return array{song_tag_id: string, name: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'song_tag_id' => $this->songTagId->value,
            'name' => $this->name->value,
            'order_no' => $this->orderNo->value,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->songTagId->equals($other->songTagId);
    }
}
