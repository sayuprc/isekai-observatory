<?php

declare(strict_types=1);

namespace Public\Application\Media\UseCase\List\Query;

use SensitiveParameter;

interface MediaQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): MediaListPage;
}
