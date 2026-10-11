<?php

declare(strict_types=1);

namespace Media\Infrastructures\Admin;

use Media\Application\Admin\Query\MediaUsageCount;
use Media\Application\Admin\Query\MediaUsageCountQueryServiceInterface;
use Media\Domain\Models\MediaId;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;

readonly class MediaUsageCountQueryService implements MediaUsageCountQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function countUsages(array $mediaIds): array
    {
        // 件数は渡された ID でまとめて引き、メディアごとの問い合わせを避ける
        $binMediaIds = array_map(fn (MediaId $mediaId): string => $this->converter->toBin($mediaId->value), $mediaIds);
        $songCounts = $this->queryFactory->countBy('song_media_links', 'media_id', $binMediaIds);
        $eventCounts = $this->queryFactory->countBy('event_media', 'media_id', $binMediaIds);

        $usages = [];
        foreach ($binMediaIds as $binMediaId) {
            $usages[$this->converter->toUuid($binMediaId)] = new MediaUsageCount(
                $songCounts[$binMediaId] ?? 0,
                $eventCounts[$binMediaId] ?? 0,
            );
        }

        return $usages;
    }
}
