<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Update;

use Song\Application\Admin\Assemble\AssembledSong;

readonly class UpdateOutputData
{
    public function __construct(public AssembledSong $song)
    {
    }
}
