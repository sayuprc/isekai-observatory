<?php

declare(strict_types=1);

namespace Release\Application\Admin\Query;

/**
 * リリース収録曲の read model。楽曲を参照するトラックは楽曲ごとに 1 件で、title は楽曲の正式名 (上書き名は反映しない)
 */
readonly class ReleaseReferencedSong
{
    public function __construct(
        public int $mediumPosition,
        public int $trackNo,
        public ?string $songId,
        public string $title,
    ) {
    }
}
