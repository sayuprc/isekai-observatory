<?php

declare(strict_types=1);

namespace Venue\Application\Public\Query;

use SensitiveParameter;

interface VenueQueryServiceInterface
{
    public function list(#[SensitiveParameter] ?string $pageToken, int $pageSize): VenueListPage;
}
