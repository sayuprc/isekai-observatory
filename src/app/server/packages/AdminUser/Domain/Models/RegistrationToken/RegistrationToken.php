<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models\RegistrationToken;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\Role;
use DateTimeImmutable;

readonly class RegistrationToken
{
    public function __construct(
        public RegistrationTokenId $registrationTokenId,
        public HashedTokenValue $token,
        public Email $email,
        public Role $role,
        public Permissions $permissions,
        public ExpiredAt $expiredAt,
        public ConsumptionStatus $status,
    ) {
    }

    /**
     * @param list<string> $permissions
     */
    public static function reconstruct(
        string $registrationTokenId,
        string $token,
        string $email,
        int $role,
        array $permissions,
        DateTimeImmutable $expiredAt,
        int $status,
    ): self {
        return new self(
            new RegistrationTokenId($registrationTokenId),
            new HashedTokenValue($token),
            new Email($email),
            Role::from($role),
            Permissions::reconstruct($permissions),
            new ExpiredAt($expiredAt),
            ConsumptionStatus::from($status),
        );
    }

    /**
     * @return array{admin_user_registration_token_id: string, token: string, email: string, role: value-of<Role>, permissions: list<string>, expired_at: string, status: value-of<ConsumptionStatus>}
     */
    public function toArray(): array
    {
        return [
            'admin_user_registration_token_id' => $this->registrationTokenId->value,
            'token' => $this->token->value,
            'email' => $this->email->value,
            'role' => $this->role->value,
            'permissions' => $this->permissions->toArray(),
            'expired_at' => $this->expiredAt->value->format('Y-m-d H:i:s'),
            'status' => $this->status->value,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->registrationTokenId->equals($other->registrationTokenId);
    }

    public function isAvailable(DateTimeImmutable $now): bool
    {
        return $this->status->isAvailable() && ! $this->expiredAt->isExpired($now);
    }

    public function consume(): self
    {
        return new self(
            $this->registrationTokenId,
            $this->token,
            $this->email,
            $this->role,
            $this->permissions,
            $this->expiredAt,
            ConsumptionStatus::Consumed,
        );
    }
}
