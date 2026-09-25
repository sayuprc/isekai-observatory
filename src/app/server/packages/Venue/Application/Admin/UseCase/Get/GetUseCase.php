<?php

declare(strict_types=1);

namespace Venue\Application\Admin\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Venue\Domain\Models\VenueId;
use Venue\Domain\Models\VenueRepositoryInterface;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private VenueRepositoryInterface $repository,
    ) {
    }

    public function handle(GetInputData $inputData): GetOutputData
    {
        $this->authorizer->authorize(Permission::ReadVenue);
        $venueId = new VenueId($inputData->venueId);
        $venue = $this->repository->find($venueId);

        if ($venue === null) {
            throw new ResourceNotFoundException('Venue', $venueId->value);
        }

        return new GetOutputData($venue);
    }
}
