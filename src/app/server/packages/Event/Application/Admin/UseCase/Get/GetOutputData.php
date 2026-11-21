<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Get;

use Event\Application\Admin\Assemble\AssembledEvent;

readonly class GetOutputData
{
    public function __construct(public AssembledEvent $event)
    {
    }
}
