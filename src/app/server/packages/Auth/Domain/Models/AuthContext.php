<?php

declare(strict_types=1);

namespace Auth\Domain\Models;

use AdminUser\Domain\Models\AdminUser;

class AuthContext
{
    private ?AdminUser $user = null;

    public function set(AdminUser $user): void
    {
        $this->user = $user;
    }

    public function get(): ?AdminUser
    {
        return $this->user;
    }
}
