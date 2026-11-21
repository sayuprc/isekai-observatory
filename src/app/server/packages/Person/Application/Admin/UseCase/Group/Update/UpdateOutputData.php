<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Group\Update;

use Person\Application\Admin\Query\PersonGroupSummary;

readonly class UpdateOutputData
{
    public function __construct(public PersonGroupSummary $personGroup)
    {
    }
}
