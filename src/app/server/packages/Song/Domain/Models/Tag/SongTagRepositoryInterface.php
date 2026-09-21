<?php

declare(strict_types=1);

namespace Song\Domain\Models\Tag;

use Song\Domain\Criteria\Tag\SongTagSearchCriteria;

interface SongTagRepositoryInterface
{
    /**
     * @return array<SongTag>
     */
    public function all(): array;

    /**
     * @return array<SongTag>
     */
    public function search(SongTagSearchCriteria $criteria): array;

    public function maxPage(SongTagSearchCriteria $criteria): int;

    public function find(SongTagId $songTagId): ?SongTag;

    public function findByName(SongTagName $name): ?SongTag;

    /**
     * @return array<SongTag>
     */
    public function findByIds(SongTagId ...$songTagIds): array;

    public function save(SongTag $tag): SongTag;

    public function isUsed(SongTagId $songTagId): bool;

    public function delete(SongTagId $songTagId): void;

    public function getMaxOrderNo(): int;
}
