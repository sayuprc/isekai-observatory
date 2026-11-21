<?php

declare(strict_types=1);

namespace Song\Application\Public\List;

use SensitiveParameter;
use Song\Application\Public\List\Query\SongListItem;

readonly class ListOutputData
{
    /**
     * @param list<SongListItem> $songs
     */
    public function __construct(
        public array $songs,
        #[SensitiveParameter]
        public ?string $nextPageToken,
    ) {
    }
}
