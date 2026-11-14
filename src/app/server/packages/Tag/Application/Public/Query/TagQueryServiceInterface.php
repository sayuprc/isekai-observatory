<?php

declare(strict_types=1);

namespace Tag\Application\Public\Query;

use SensitiveParameter;

interface TagQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): TagListPage;
}
