<?php

declare(strict_types=1);

namespace Tag\Domain\Models;

readonly class TagReference
{
    public function __construct(public TagId $tagId)
    {
    }

    public static function reconstruct(string $tagId): self
    {
        return new self(new TagId($tagId));
    }

    /**
     * @return array{tag_id: string}
     */
    public function toArray(): array
    {
        return [
            'tag_id' => $this->tagId->value,
        ];
    }
}
