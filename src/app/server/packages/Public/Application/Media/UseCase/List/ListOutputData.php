<?php

declare(strict_types=1);

namespace Public\Application\Media\UseCase\List;

use Public\Application\Media\UseCase\List\Query\MediaListItem;
use SensitiveParameter;

readonly class ListOutputData
{
    /**
     * @param list<MediaListItem> $media
     */
    public function __construct(
        public array $media,
        #[SensitiveParameter]
        public ?string $nextPageToken,
    ) {
    }
}
