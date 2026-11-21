<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Create;

use Song\Application\Admin\Assemble\AssembledSong;

readonly class CreateOutputData
{
    public function __construct(public AssembledSong $song)
    {
    }
}
