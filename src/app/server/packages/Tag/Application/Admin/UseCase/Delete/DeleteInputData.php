<?php

declare(strict_types=1);

namespace Tag\Application\Admin\UseCase\Delete;

readonly class DeleteInputData
{
    public function __construct(public string $tagId)
    {
    }
}
