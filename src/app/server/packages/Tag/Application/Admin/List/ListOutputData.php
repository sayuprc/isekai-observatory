<?php

declare(strict_types=1);

namespace Tag\Application\Admin\List;

use Tag\Domain\Models\Tag;

readonly class ListOutputData
{
    /**
     * @param array<Tag> $tags
     */
    public function __construct(public array $tags)
    {
    }
}
