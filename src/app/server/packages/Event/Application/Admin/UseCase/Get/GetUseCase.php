<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Get;

use AdminUser\Domain\Models\Permission;
use Event\Application\Admin\Assemble\EventAssembler;
use Event\Domain\Models\EventId;
use Event\Domain\Models\EventRepositoryInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\ResourceNotFoundException;

readonly class GetUseCase
{
    public function __construct(
        private UseCaseAuthorizer $authorizer,
        private EventRepositoryInterface $repository,
        private EventAssembler $assembler,
    ) {
    }

    public function handle(GetInputData $inputData): GetOutputData
    {
        $this->authorizer->authorize(Permission::ReadEvent);

        $event = $this->repository->find(new EventId($inputData->eventId));

        if (is_null($event)) {
            throw new ResourceNotFoundException('Event', $inputData->eventId);
        }

        return new GetOutputData($this->assembler->assemble($event));
    }
}
