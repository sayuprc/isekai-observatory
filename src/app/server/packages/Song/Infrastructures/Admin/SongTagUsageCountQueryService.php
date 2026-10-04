<?php

declare(strict_types=1);

namespace Song\Infrastructures\Admin;

use Override;
use Song\Application\Admin\Query\Tag\SongTagUsageCountQueryServiceInterface;
use Song\Domain\Models\Tag\SongTagId;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;

readonly class SongTagUsageCountQueryService implements SongTagUsageCountQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function countSongs(array $songTagIds): array
    {
        // 件数は渡された ID でまとめて引き、1 件ごとの問い合わせを避ける
        $binIds = array_map(fn (SongTagId $id): string => $this->converter->toBin($id->value), $songTagIds);
        $counts = $this->queryFactory->countBy('song_taggings', 'song_tag_id', $binIds);

        $result = [];
        foreach ($binIds as $binId) {
            $result[$this->converter->toUuid($binId)] = $counts[$binId] ?? 0;
        }

        return $result;
    }
}
