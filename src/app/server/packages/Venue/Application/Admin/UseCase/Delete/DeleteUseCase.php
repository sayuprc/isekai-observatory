<?php

declare(strict_types=1);

namespace Venue\Application\Admin\UseCase\Delete;

use AdminUser\Domain\Models\Permission;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Venue\Domain\Models\VenueId;
use Venue\Domain\Models\VenueRepositoryInterface;

readonly class DeleteUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private VenueRepositoryInterface $repository,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(DeleteInputData $inputData): void
    {
        $this->authorizer->authorize(Permission::WriteVenue);
        $venueId = new VenueId($inputData->venueId);

        $this->transaction->scope(function () use ($venueId): void {
            $venue = $this->repository->find($venueId);

            if (is_null($venue)) {
                throw new ResourceNotFoundException('Venue', $venueId->value);
            }
            if ($this->repository->isUsed($venueId)) {
                throw new BusinessRuleViolationException('この開催先はイベントに使用されているため削除できません');
            }

            $this->repository->delete($venueId);
            $this->recorder->record(
                AuditAction::Delete,
                AuditTargetType::Venue,
                $venue->venueId,
                $venue->toArray(),
            );
        });
    }
}
