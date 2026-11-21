<?php

declare(strict_types=1);

namespace Media\Application\Public\UseCase\List\Query;

use SensitiveParameter;

interface MediaQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): MediaListPage;
}
