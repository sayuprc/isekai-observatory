<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Create;

use Media\Domain\Models\Media;

readonly class CreateOutputData
{
    public function __construct(public Media $media)
    {
    }
}
