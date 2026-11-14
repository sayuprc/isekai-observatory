<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models\RegistrationToken;

use AdminUser\Domain\Models\Email;
use SensitiveParameter;

interface RegistrationTokenRepositoryInterface
{
    public function save(#[SensitiveParameter] RegistrationToken $token): RegistrationToken;

    public function findByEmailForUpdate(Email $email): ?RegistrationToken;

    /**
     * @return array<int, RegistrationToken>
     */
    public function findUnusedByEmailForUpdate(Email $email): array;
}
