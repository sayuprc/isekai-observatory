<?php

declare(strict_types=1);

namespace Event\Application\Admin\Update;

use Event\Application\Admin\Assemble\AssembledEvent;

readonly class UpdateOutputData
{
    public function __construct(public AssembledEvent $event)
    {
    }
}
