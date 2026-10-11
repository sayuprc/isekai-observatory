<?php

declare(strict_types=1);

namespace Media\Application\Admin\Query;

readonly class MediaReferencedSong
{
    public function __construct(
        public string $songId,
        public string $title,
        public int $songOrderNo,
        public int $mediaOrderNo,
    ) {
    }
}
