<?php

declare(strict_types=1);

namespace Tag\Application\Admin\UseCase\Get;

use Tag\Domain\Models\Tag;

readonly class GetOutputData
{
    public function __construct(public Tag $tag)
    {
    }
}
