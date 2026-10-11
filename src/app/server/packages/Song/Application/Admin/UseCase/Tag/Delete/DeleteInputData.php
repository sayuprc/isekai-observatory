<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Tag\Delete;

readonly class DeleteInputData
{
    public function __construct(public string $songTagId)
    {
    }
}
