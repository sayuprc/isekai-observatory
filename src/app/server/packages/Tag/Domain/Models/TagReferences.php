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
     * @param array<int, array{tagId: string}> $items
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
     * @param array<int, array{tagId: string}> $items
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

    /**
     * 付いていないタグだけを末尾に足す
     */
    public function with(TagId ...$tagIds): self
    {
        $items = $this->items;
        $existing = array_map(static fn (TagReference $item): string => $item->tagId->value, $items);

        foreach ($tagIds as $tagId) {
            if (in_array($tagId->value, $existing, true)) {
                continue;
            }

            $items[] = new TagReference($tagId);
            $existing[] = $tagId->value;
        }

        return new self($items);
    }

    /**
     * 付いているタグだけを外す
     */
    public function without(TagId ...$tagIds): self
    {
        $removed = array_map(static fn (TagId $tagId): string => $tagId->value, $tagIds);

        return new self(array_values(array_filter(
            $this->items,
            static fn (TagReference $item): bool => ! in_array($item->tagId->value, $removed, true),
        )));
    }
}
