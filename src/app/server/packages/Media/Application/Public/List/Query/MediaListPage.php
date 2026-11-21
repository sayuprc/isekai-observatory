<?php

declare(strict_types=1);

namespace Media\Application\Public\List\Query;

use SensitiveParameter;

readonly class MediaListPage
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
