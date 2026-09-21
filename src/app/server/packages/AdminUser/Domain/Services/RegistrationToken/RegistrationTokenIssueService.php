<?php

declare(strict_types=1);

namespace AdminUser\Domain\Services\RegistrationToken;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus;
use AdminUser\Domain\Models\RegistrationToken\ExpiredAt;
use AdminUser\Domain\Models\RegistrationToken\HashedTokenValue;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenId;
use AdminUser\Domain\Models\Role;
use Support\Contracts\ClockInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;

class RegistrationTokenIssueService
{
    private const int TTL_DAY = 7;

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly UuidGeneratorInterface $uuidGenerator,
        private readonly RandomTokenGeneratorInterface $randomTokenGenerator,
        private readonly TokenHasherInterface $tokenHasher,
    ) {
    }

    /**
     * @return array{token: RegistrationToken, plainToken: string}
     */
    public function issue(Email $email, Role $role, Permissions $permissions): array
    {
        $plainToken = $this->randomTokenGenerator->generate();

        $token = new RegistrationToken(
            new RegistrationTokenId($this->uuidGenerator->generate()),
            new HashedTokenValue($this->tokenHasher->hash($plainToken)),
            $email,
            $role,
            $permissions,
            new ExpiredAt($this->clock->now()->modify('+' . self::TTL_DAY . ' days')),
            ConsumptionStatus::Unused,
        );

        return [
            'token' => $token,
            'plainToken' => $plainToken,
        ];
    }
}
