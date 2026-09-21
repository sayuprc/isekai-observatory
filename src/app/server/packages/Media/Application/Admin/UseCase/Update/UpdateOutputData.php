<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Update;

use Media\Domain\Models\Media;

readonly class UpdateOutputData
{
    public function __construct(public Media $media)
    {
    }
}
