<?php

declare(strict_types=1);

namespace Media\Application\Viewer\List;

use Media\Application\Viewer\List\Query\MediaListItem;

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
