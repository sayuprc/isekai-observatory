<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Get;

use Media\Application\Admin\Query\MediaReferencedSong;
use Media\Domain\Models\Media;

readonly class GetOutputData
{
    /**
     * @param list<MediaReferencedSong> $songs
     */
    public function __construct(
        public Media $media,
        public array $songs,
    ) {
    }
}
