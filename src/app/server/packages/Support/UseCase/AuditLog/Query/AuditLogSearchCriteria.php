<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog\Query;

use DateTimeImmutable;
use Support\Domain\SearchCriteria\PerPage;
use Support\Domain\ValueObjects\String\TextNormalizer;
use Support\Optional\Optional;
use Support\Optional\Some;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;

readonly class AuditLogSearchCriteria
{
    /** @var Optional<string> */
    public Optional $adminUserName;

    /**
     * @param Optional<DateTimeImmutable> $from
     * @param Optional<DateTimeImmutable> $to
     * @param Optional<AuditAction>       $action
     * @param Optional<AuditTargetType>   $targetType
     * @param Optional<string>            $targetId      識別子のため正規化しない (バイト保持)
     * @param Optional<string>            $adminUserName
     */
    public function __construct(
        public Optional $from,
        public Optional $to,
        public Optional $action,
        public Optional $targetType,
        public Optional $targetId,
        Optional $adminUserName,
        public int $page = 1,
        public PerPage $perPage = PerPage::Fifty,
    ) {
        // 保存側の AdminUserName と対称に、検索語も NFC へ揃える
        $this->adminUserName = $adminUserName->isPresent()
            ? new Some(TextNormalizer::toNfc($adminUserName->get()))
            : $adminUserName;
    }
}
