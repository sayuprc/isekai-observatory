<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\Create;

use Song\Domain\Models\Tag\SongTag;

readonly class CreateOutputData
{
    public function __construct(public SongTag $tag)
    {
    }
}
