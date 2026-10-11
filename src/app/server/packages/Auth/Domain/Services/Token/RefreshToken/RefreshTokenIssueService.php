<?php

declare(strict_types=1);

namespace Auth\Domain\Services\Token\RefreshToken;

use AdminUser\Domain\Models\AdminUserId;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Token\RefreshToken\HashedTokenValue;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Support\Contracts\ClockInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;

class RefreshTokenIssueService
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
     * @return array{token: RefreshToken, plainToken: string}
     */
    public function issue(string $adminUserId): array
    {
        $plainToken = $this->randomTokenGenerator->generate();

        $token = new RefreshToken(
            new RefreshTokenId($this->uuidGenerator->generate()),
            new AdminUserId($adminUserId),
            new HashedTokenValue($this->tokenHasher->hash($plainToken)),
            new ExpiredAt($this->clock->now()->modify('+' . self::TTL_DAY . ' days')),
            ConsumptionStatus::Unused,
        );

        return [
            'token' => $token,
            'plainToken' => $plainToken,
        ];
    }
}
