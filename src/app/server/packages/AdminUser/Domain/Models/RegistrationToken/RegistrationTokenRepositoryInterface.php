<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models\RegistrationToken;

use AdminUser\Domain\Models\Email;

interface RegistrationTokenRepositoryInterface
{
    public function save(RegistrationToken $token): RegistrationToken;

    public function findByEmailForUpdate(Email $email): ?RegistrationToken;

    /**
     * @return list<RegistrationToken>
     */
    public function findUnusedByEmailForUpdate(Email $email): array;
}
