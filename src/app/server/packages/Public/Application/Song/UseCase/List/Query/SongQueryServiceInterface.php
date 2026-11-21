<?php

declare(strict_types=1);

namespace Public\Application\Song\UseCase\List\Query;

use SensitiveParameter;

interface SongQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): SongListPage;
}
