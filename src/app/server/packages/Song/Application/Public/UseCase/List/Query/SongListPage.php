<?php

declare(strict_types=1);

namespace Song\Application\Public\UseCase\List\Query;

use SensitiveParameter;

readonly class SongListPage
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
