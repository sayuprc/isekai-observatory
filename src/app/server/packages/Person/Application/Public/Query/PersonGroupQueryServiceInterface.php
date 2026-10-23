<?php

declare(strict_types=1);

namespace Person\Application\Public\Query;

interface PersonGroupQueryServiceInterface
{
    public function list(?string $pageToken, int $pageSize): PersonGroupListPage;
}
