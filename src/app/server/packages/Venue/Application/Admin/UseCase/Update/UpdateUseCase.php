<?php

declare(strict_types=1);

namespace Venue\Application\Admin\UseCase\Update;

use AdminUser\Domain\Models\Permission;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Venue\Domain\Models\VenueId;
use Venue\Domain\Models\VenueRepositoryInterface;
use Venue\Domain\Services\VenueIntegrityService;

readonly class UpdateUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private TransactionInterface $transaction,
        private VenueRepositoryInterface $repository,
        private VenueIntegrityService $service,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    public function handle(UpdateInputData $inputData): UpdateOutputData
    {
        $this->authorizer->authorize(Permission::WriteVenue);
        $venueId = new VenueId($inputData->venueId);

        return $this->transaction->scope(function () use ($inputData, $venueId): UpdateOutputData {
            if ($this->repository->find($venueId) === null) {
                throw new ResourceNotFoundException('Venue', $venueId->value);
            }

            $venue = $this->service->prepareForUpdate($inputData->venueId, $inputData->name, $inputData->kind);
            $this->repository->save($venue);
            $this->recorder->record(
                AuditAction::Update,
                AuditTargetType::Venue,
                $venue->venueId,
                $venue->toArray(),
            );

            return new UpdateOutputData($venue);
        });
    }
}
