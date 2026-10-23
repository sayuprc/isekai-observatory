<?php

declare(strict_types=1);

namespace Media\Application\Public\Query;

readonly class MediaListPage
{
    /**
     * @param list<MediaListItem> $media
     */
    public function __construct(
        public array $media,
        public ?string $nextPageToken,
    ) {
    }
}
