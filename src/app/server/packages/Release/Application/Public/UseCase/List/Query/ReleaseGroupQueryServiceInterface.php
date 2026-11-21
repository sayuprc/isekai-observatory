<?php

declare(strict_types=1);

namespace Release\Application\Public\UseCase\List\Query;

use SensitiveParameter;

interface ReleaseGroupQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): ReleaseGroupListPage;
}
