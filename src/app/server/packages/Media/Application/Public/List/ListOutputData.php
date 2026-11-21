<?php

declare(strict_types=1);

namespace Media\Application\Public\List;

use Media\Application\Public\List\Query\MediaListItem;
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
