<?php

declare(strict_types=1);

namespace Tag\Domain\Models;

use Tag\Domain\Criteria\TagSearchCriteria;

interface TagRepositoryInterface
{
    /**
     * @return array<Tag>
     */
    public function all(): array;

    /**
     * @return array<Tag>
     */
    public function search(TagSearchCriteria $criteria): array;

    public function maxPage(TagSearchCriteria $criteria): int;

    public function find(TagId $tagId): ?Tag;

    public function findByName(TagName $name): ?Tag;

    /**
     * @return array<Tag>
     */
    public function findByIds(TagId ...$tagIds): array;

    public function save(Tag $tag): Tag;

    public function isUsed(TagId $tagId): bool;

    public function delete(TagId $tagId): void;

    public function getMaxOrderNo(): int;
}
