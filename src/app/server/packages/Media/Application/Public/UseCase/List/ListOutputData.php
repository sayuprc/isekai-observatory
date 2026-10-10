<?php

declare(strict_types=1);

namespace Media\Application\Public\UseCase\List;

use Media\Application\Public\Query\MediaListItem;

readonly class ListOutputData
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
