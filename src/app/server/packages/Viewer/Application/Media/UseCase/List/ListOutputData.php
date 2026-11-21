<?php

declare(strict_types=1);

namespace Viewer\Application\Media\UseCase\List;

use Viewer\Application\Media\UseCase\List\Query\MediaListItem;

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
