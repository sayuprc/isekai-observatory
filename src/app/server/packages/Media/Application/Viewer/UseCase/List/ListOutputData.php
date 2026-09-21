<?php

declare(strict_types=1);

namespace Media\Application\Viewer\UseCase\List;

use Media\Application\Viewer\Query\MediaListItem;

readonly class ListOutputData
{
    /**
     * @param array<MediaListItem> $media
     */
    public function __construct(
        public array $media,
        public ?string $nextCursor,
    ) {
    }
}
