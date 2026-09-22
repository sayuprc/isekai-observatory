<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use Event\Domain\Models\EventRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private EventRepositoryInterface $repository,
    ) {
    }

    public function handle(GetInputData $inputData): GetOutputData
    {
        $this->authorizer->authorize(Permission::ReadEvent);
        $event = $this->repository->find($inputData->eventId);
        if ($event === null) {
            throw new ResourceNotFoundException('Event', $inputData->eventId);
        }

        return new GetOutputData($event);
    }
}
