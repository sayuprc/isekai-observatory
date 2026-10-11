<?php

declare(strict_types=1);

namespace Release\Application\Admin\Query;

/**
 * リリース収録曲の read model。参照トラックの title は楽曲の正式名 (上書き名は反映しない)
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
