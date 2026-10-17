<?php

declare(strict_types=1);

namespace Tag\Domain\Models;

use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\BusinessRuleViolationException;

/**
 * @extends ImmutableCollection<int, TagReference>
 */
readonly class TagReferences extends ImmutableCollection
{
    /**
     * @param list<array{tagId: string}> $items
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(array $items): self
    {
        $tags = [];
        $seen = [];

        foreach ($items as $item) {
            $tag = new TagReference(new TagId($item['tagId']));

            if (isset($seen[$tag->tagId->value])) {
                throw new BusinessRuleViolationException('同じタグを複数指定することはできません。');
            }

            $seen[$tag->tagId->value] = true;
            $tags[] = $tag;
        }

        return new self($tags);
    }

    /**
     * @param list<array{tagId: string}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(static fn (array $item): TagReference => TagReference::reconstruct(...$item), $items));
    }

    /**
     * @return list<array{tag_id: string}>
     */
    public function toArray(): array
    {
        return $this->toGeneric()
            ->map(static fn (TagReference $item): array => $item->toArray())
            ->toList();
    }
}
