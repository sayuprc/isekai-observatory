<?php

declare(strict_types=1);

namespace Tag\Domain\Models;

use Support\Domain\Exceptions\BusinessRuleViolationException;

/**
 * 一括操作で記録に加えるタグの付与と除去
 */
readonly class TagChanges
{
    /**
     * @param list<TagId> $addTagIds
     * @param list<TagId> $removeTagIds
     *
     * @throws BusinessRuleViolationException
     */
    public function __construct(
        public array $addTagIds,
        public array $removeTagIds,
    ) {
        $added = array_map(static fn (TagId $tagId): string => $tagId->value, $addTagIds);
        $removed = array_map(static fn (TagId $tagId): string => $tagId->value, $removeTagIds);

        if (array_intersect($added, $removed) !== []) {
            throw new BusinessRuleViolationException('同じタグを付与と除去の両方に指定することはできません。');
        }
    }

    /**
     * @param list<string> $addTagIds
     * @param list<string> $removeTagIds
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromIds(array $addTagIds, array $removeTagIds): self
    {
        return new self(
            array_map(static fn (string $id): TagId => new TagId($id), array_values(array_unique($addTagIds))),
            array_map(static fn (string $id): TagId => new TagId($id), array_values(array_unique($removeTagIds))),
        );
    }

    public function isEmpty(): bool
    {
        return $this->addTagIds === [] && $this->removeTagIds === [];
    }

    public function apply(TagReferences $tags): TagReferences
    {
        return $tags->with(...$this->addTagIds)->without(...$this->removeTagIds);
    }
}
