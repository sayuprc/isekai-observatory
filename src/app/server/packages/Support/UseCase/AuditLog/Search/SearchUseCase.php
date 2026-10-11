<?php

declare(strict_types=1);

namespace Support\UseCase\AuditLog\Search;

use AdminUser\Domain\Models\Permission;
use Support\Optional\Arg;
use Support\Optional\None;
use Support\Optional\Some;
use Support\UseCase\AuditLog\Query\AuditLogQueryServiceInterface;
use Support\UseCase\AuditLog\Query\AuditLogSearchCriteria;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class SearchUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private AuditLogQueryServiceInterface $query,
    ) {
    }

    public function handle(SearchInputData $inputData): SearchOutputData
    {
        $this->authorizer->authorize(Permission::ReadAuditLog);

        $criteria = new AuditLogSearchCriteria(
            $inputData->from === Arg::Optional ? new None() : new Some($inputData->from),
            $inputData->to === Arg::Optional ? new None() : new Some($inputData->to),
            $inputData->action === Arg::Optional ? new None() : new Some($inputData->action),
            $inputData->targetType === Arg::Optional ? new None() : new Some($inputData->targetType),
            $inputData->targetId === Arg::Optional ? new None() : new Some($inputData->targetId),
            $inputData->adminUserName === Arg::Optional ? new None() : new Some($inputData->adminUserName),
            $inputData->page,
            $inputData->perPage,
        );

        return new SearchOutputData(
            $this->query->search($criteria),
            $this->query->maxPage($criteria),
        );
    }
}
