<?php

declare(strict_types=1);

namespace Tag\Application\Admin\UseCase\Create;

use Tag\Domain\Models\Tag;

readonly class CreateOutputData
{
    public function __construct(public Tag $tag)
    {
    }
}
