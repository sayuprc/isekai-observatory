<?php

declare(strict_types=1);

namespace Person\Application\Public\UseCase\List\Query;

use SensitiveParameter;

interface PersonQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): PersonListPage;
}
