<?php

declare(strict_types=1);

namespace Public\Application\Release\UseCase\List\Query;

readonly class ReleaseTrackSongItem
{
    /**
     * @param ?string $songId 参照先の楽曲が非公開なら null
     */
    public function __construct(
        public ?string $songId,
        public string $title,
    ) {
    }
}
