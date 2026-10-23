<?php

declare(strict_types=1);

namespace Song\Application\Public\Query;

interface SongQueryServiceInterface
{
    public function list(?string $pageToken, int $pageSize): SongListPage;
}
