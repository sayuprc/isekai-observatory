<?php

declare(strict_types=1);

namespace Auth\Domain\Models\RecoveryCode;

use AdminUser\Domain\Models\AdminUserId;
use DateTimeImmutable;

readonly class RecoveryCode
{
    public function __construct(
        public RecoveryCodeId $recoveryCodeId,
        public AdminUserId $adminUserId,
        public HashedCodeValue $code,
        public ConsumptionStatus $status,
        public ?DateTimeImmutable $usedAt,
    ) {
    }

    public static function reconstruct(
        string $recoveryCodeId,
        string $adminUserId,
        string $code,
        int $status,
        ?DateTimeImmutable $usedAt,
    ): self {
        return new self(
            new RecoveryCodeId($recoveryCodeId),
            new AdminUserId($adminUserId),
            new HashedCodeValue($code),
            ConsumptionStatus::from($status),
            $usedAt,
        );
    }

    /**
     * @return array{admin_user_recovery_code_id: string, admin_user_id: string, code: string, status: value-of<ConsumptionStatus>, used_at: string|null}
     */
    public function toArray(): array
    {
        return [
            'admin_user_recovery_code_id' => $this->recoveryCodeId->value,
            'admin_user_id' => $this->adminUserId->value,
            'code' => $this->code->value,
            'status' => $this->status->value,
            'used_at' => $this->usedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public function equals(self $other): bool
    {
        return $this->recoveryCodeId->equals($other->recoveryCodeId);
    }

    public function isAvailable(): bool
    {
        return $this->status->isAvailable();
    }

    public function consume(DateTimeImmutable $now): self
    {
        return new self(
            $this->recoveryCodeId,
            $this->adminUserId,
            $this->code,
            ConsumptionStatus::Consumed,
            $now,
        );
    }
}
