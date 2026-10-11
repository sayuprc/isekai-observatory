<?php

declare(strict_types=1);

namespace Media\Domain\Criteria;

enum Sort: string
{
    case PublishedAt = 'published_at';

    case Title = 'title';
}
