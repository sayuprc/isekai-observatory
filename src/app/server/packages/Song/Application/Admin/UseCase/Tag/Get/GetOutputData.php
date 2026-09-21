<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\Get;

use Song\Domain\Models\Tag\SongTag;

readonly class GetOutputData
{
    public function __construct(public SongTag $tag)
    {
    }
}
