<?php

declare(strict_types=1);

namespace Media\Application\Viewer\UseCase\List\Query;

readonly class MediaListPage
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
