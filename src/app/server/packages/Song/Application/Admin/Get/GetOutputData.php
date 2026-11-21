<?php

declare(strict_types=1);

namespace Song\Application\Admin\Get;

use Song\Application\Admin\Assemble\AssembledSong;

readonly class GetOutputData
{
    public function __construct(public AssembledSong $song)
    {
    }
}
