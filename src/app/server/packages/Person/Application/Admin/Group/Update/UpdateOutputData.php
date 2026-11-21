<?php

declare(strict_types=1);

namespace Person\Application\Admin\Group\Update;

use Person\Application\Admin\Group\Query\PersonGroupSummary;

readonly class UpdateOutputData
{
    public function __construct(public PersonGroupSummary $personGroup)
    {
    }
}
