<?php

declare(strict_types=1);

namespace Viewer\Application\Song\UseCase\List;

use Viewer\Application\Song\UseCase\List\Query\SongListItem;

readonly class ListOutputData
{
    /**
     * @param array<SongListItem> $songs
     */
    public function __construct(
        public array $songs,
        public ?string $nextCursor,
    ) {
    }
}
