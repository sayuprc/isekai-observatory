<?php

declare(strict_types=1);

namespace Tag\Application\Public\Query;

interface TagQueryServiceInterface
{
    public function list(?string $pageToken, int $pageSize): TagListPage;
}
