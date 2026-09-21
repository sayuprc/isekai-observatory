<?php

declare(strict_types=1);

namespace Song\Application\Admin\UseCase\Type;

use AdminUser\Domain\Models\Permission;
use Song\Domain\Models\SongType;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

readonly class ListUseCase
{
    public function __construct(private UseCaseAuthorizer $authorizer)
    {
    }

    public function handle(): ListOutputData
    {
        $this->authorizer->authorize(Permission::ReadSong);

        return new ListOutputData(SongType::cases());
    }
}
