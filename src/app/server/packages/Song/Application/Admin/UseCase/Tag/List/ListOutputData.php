<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\List;

use Song\Domain\Models\Tag\SongTag;

readonly class ListOutputData
{
    /**
     * @param array<SongTag> $tags
     */
    public function __construct(public array $tags)
    {
    }
}
