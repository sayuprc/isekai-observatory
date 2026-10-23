<?php

declare(strict_types=1);

namespace Release\Application\Public\Query;

interface ReleaseGroupQueryServiceInterface
{
    public function list(?string $pageToken, int $pageSize): ReleaseGroupListPage;
}
