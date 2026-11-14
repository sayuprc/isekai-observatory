<?php

declare(strict_types=1);

namespace Song\Application\Public\UseCase\List;

use SensitiveParameter;
use Song\Application\Public\Query\SongListItem;

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
