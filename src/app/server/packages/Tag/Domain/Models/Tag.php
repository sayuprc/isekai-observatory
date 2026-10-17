<?php

declare(strict_types=1);

namespace Tag\Domain\Models;

use Support\Domain\ValueObjects\OrderNo;

readonly class Tag
{
    public function __construct(
        public TagId $tagId,
        public TagName $name,
        public OrderNo $orderNo,
    ) {
    }

    public static function reconstruct(string $tagId, string $name, int $orderNo): self
    {
        return new self(
            new TagId($tagId),
            new TagName($name),
            new OrderNo($orderNo),
        );
    }

    /**
     * @return array{tag_id: string, name: string, order_no: int}
     */
    public function toArray(): array
    {
        return [
            'tag_id' => $this->tagId->value,
            'name' => $this->name->value,
            'order_no' => $this->orderNo->value,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->tagId->equals($other->tagId);
    }
}
