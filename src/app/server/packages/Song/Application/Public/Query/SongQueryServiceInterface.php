<?php

declare(strict_types=1);

namespace Song\Application\Public\Query;

use SensitiveParameter;

interface SongQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): SongListPage;
}
