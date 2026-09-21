<?php

declare(strict_types=1);

namespace AdminUser\Application\Admin\UseCase\List;

use AdminUser\Domain\Models\AdminUser;

readonly class ListOutputData
{
    /**
     * @param array<AdminUser> $adminUsers
     */
    public function __construct(public array $adminUsers)
    {
    }
}
