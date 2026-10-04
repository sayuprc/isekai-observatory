<?php

declare(strict_types=1);

namespace Song\Application\Admin\Query\Tag;

use Song\Domain\Models\Tag\SongTagId;

interface SongTagUsageCountQueryServiceInterface
{
    /**
     * このタグが付いている楽曲の件数を、楽曲タグ ID (UUID)をキーにして返す。管理画面の一覧に出す
     *
     * @param list<SongTagId> $songTagIds
     *
     * @return array<string, int>
     */
    public function countSongs(array $songTagIds): array;
}
