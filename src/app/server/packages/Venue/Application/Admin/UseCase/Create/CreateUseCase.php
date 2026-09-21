<?php

declare(strict_types=1);

namespace Venue\Application\Admin\UseCase\Create;

use AdminUser\Domain\Models\Permission;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Venue\Domain\Models\VenueRepositoryInterface;
use Venue\Domain\Services\VenueIntegrityService;

readonly class CreateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private VenueRepositoryInterface $repository,
        private VenueIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(CreateInputData $inputData): CreateOutputData
    {
        $this->authorizer->authorize(Permission::WriteVenue);

        return $this->transaction->scope(function () use ($inputData): CreateOutputData {
            $venue = $this->service->prepareForCreate($inputData->name, $inputData->kind);
            $this->repository->save($venue);
            $this->recorder->record(
                AuditAction::Create,
                AuditTargetType::Venue,
                $venue->venueId,
                $venue->toArray(),
            );

            return new CreateOutputData($venue);
        });
    }
}
