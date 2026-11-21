<?php

declare(strict_types=1);

namespace Public\Application\Person\UseCase\List\Query;

use SensitiveParameter;

interface PersonQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): PersonListPage;
}
