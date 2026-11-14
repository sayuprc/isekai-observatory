<?php

declare(strict_types=1);

namespace Person\Application\Public\Query;

use SensitiveParameter;

interface PersonGroupQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): PersonGroupListPage;
}
