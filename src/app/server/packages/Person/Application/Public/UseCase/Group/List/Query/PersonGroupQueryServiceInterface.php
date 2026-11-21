<?php

declare(strict_types=1);

namespace Person\Application\Public\UseCase\Group\List\Query;

use SensitiveParameter;

interface PersonGroupQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): PersonGroupListPage;
}
