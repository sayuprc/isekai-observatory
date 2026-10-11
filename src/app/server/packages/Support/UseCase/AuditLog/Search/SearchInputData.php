<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog\Search;

use DateTimeImmutable;
use Support\Domain\SearchCriteria\PerPage;
use Support\Optional\Arg;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;

readonly class SearchInputData
{
    public function __construct(
        public Arg|DateTimeImmutable $from = Arg::Optional,
        public Arg|DateTimeImmutable $to = Arg::Optional,
        public Arg|AuditAction $action = Arg::Optional,
        public Arg|AuditTargetType $targetType = Arg::Optional,
        public Arg|string $targetId = Arg::Optional,
        public Arg|string $adminUserName = Arg::Optional,
        public int $page = 1,
        public PerPage $perPage = PerPage::Fifty,
    ) {
    }
}
