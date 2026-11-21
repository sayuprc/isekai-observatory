<?php

declare(strict_types=1);

namespace Public\Application\Tag\UseCase\List\Query;

use SensitiveParameter;

interface TagQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): TagListPage;
}
