<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\Group\Get;

use Person\Application\Admin\Query\PersonGroupSummary;

readonly class GetOutputData
{
    public function __construct(public PersonGroupSummary $personGroup)
    {
    }
}
