<?php

declare(strict_types=1);

namespace Tag\Application\Admin\UseCase\Update;

use Tag\Domain\Models\Tag;

readonly class UpdateOutputData
{
    public function __construct(public Tag $tag)
    {
    }
}
