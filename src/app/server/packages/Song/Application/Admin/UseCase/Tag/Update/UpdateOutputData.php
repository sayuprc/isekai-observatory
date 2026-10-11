<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\Update;

use Song\Domain\Models\Tag\SongTag;

readonly class UpdateOutputData
{
    public function __construct(public SongTag $tag)
    {
    }
}
