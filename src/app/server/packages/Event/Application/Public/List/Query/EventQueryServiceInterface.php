<?php

declare(strict_types=1);

namespace Event\Application\Public\List\Query;

use SensitiveParameter;

interface EventQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): EventListPage;
}
