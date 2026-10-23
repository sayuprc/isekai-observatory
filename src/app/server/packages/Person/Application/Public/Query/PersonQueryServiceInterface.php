<?php

declare(strict_types=1);

namespace Person\Application\Public\Query;

interface PersonQueryServiceInterface
{
    public function list(?string $pageToken, int $pageSize): PersonListPage;
}
