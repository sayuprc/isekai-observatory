<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Token\RefreshToken;

use AdminUser\Domain\Models\AdminUserId;
use DateTimeImmutable;
use DateTimeInterface;

readonly class RefreshToken
{
    public function __construct(
        public RefreshTokenId $refreshTokenId,
        public AdminUserId $adminUserId,
        public HashedTokenValue $token,
        private ExpiredAt $expiredAt,
        private ConsumptionStatus $status,
    ) {
    }

    public static function reconstruct(
        string $refreshTokenId,
        string $adminUserId,
        string $token,
        DateTimeImmutable $expiredAt,
        int $status,
    ): self {
        return new self(
            new RefreshTokenId($refreshTokenId),
            new AdminUserId($adminUserId),
            new HashedTokenValue($token),
            new ExpiredAt($expiredAt),
            ConsumptionStatus::from($status),
        );
    }

    /**
     * @return array{refresh_token_id: string, admin_user_id: string, token: string, expired_at: non-falsy-string, status: value-of<ConsumptionStatus>}
     */
    public function toArray(): array
    {
        return [
            'refresh_token_id' => $this->refreshTokenId->value,
            'admin_user_id' => $this->adminUserId->value,
            'token' => $this->token->value,
            'expired_at' => $this->expiredAt->value->format('Y-m-d H:i:s'),
            'status' => $this->status->value,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->refreshTokenId->equals($other->refreshTokenId);
    }

    public function isAvailable(DateTimeInterface $now): bool
    {
        return $this->status->isAvailable() && ! $this->expiredAt->isExpired($now);
    }

    public function consume(): self
    {
        return new self(
            $this->refreshTokenId,
            $this->adminUserId,
            $this->token,
            $this->expiredAt,
            ConsumptionStatus::Consumed,
        );
    }
}
