<?php

declare(strict_types=1);

namespace Song\Domain\Models;

interface SongRepositoryInterface
{
    public function find(SongId $songId): ?Song;

    /**
     * @return array<int, Song>
     */
    public function findByIds(SongId ...$songIds): array;

    public function isUsed(SongId $songId): bool;

    public function save(Song $song): Song;

    public function delete(SongId $songId): void;

    public function getMaxOrderNo(): int;
}
