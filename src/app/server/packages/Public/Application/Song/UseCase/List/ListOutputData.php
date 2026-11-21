<?php

declare(strict_types=1);

namespace Public\Application\Song\UseCase\List;

use Public\Application\Song\UseCase\List\Query\SongListItem;
use SensitiveParameter;

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
