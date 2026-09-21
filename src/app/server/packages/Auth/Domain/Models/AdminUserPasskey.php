<?php

declare(strict_types=1);

namespace Auth\Domain\Models;

use DateTimeImmutable;

readonly class AdminUserPasskey
{
    /**
     * @param list<string> $transports
     */
    public function __construct(
        public string $adminUserPasskeyId,
        public string $adminUserId,
        public string $userHandle,
        public string $name,
        public string $credentialId,
        public string $publicKey,
        public string $aaguid,
        public array $transports,
        public ?bool $backupEligible,
        public ?bool $backupState,
        public int $signCount,
        public DateTimeImmutable $createdAt,
        public ?DateTimeImmutable $lastUsedAt,
    ) {
    }

    public function withCounter(int $signCount, DateTimeImmutable $lastUsedAt): self
    {
        return new self(
            $this->adminUserPasskeyId,
            $this->adminUserId,
            $this->userHandle,
            $this->name,
            $this->credentialId,
            $this->publicKey,
            $this->aaguid,
            $this->transports,
            $this->backupEligible,
            $this->backupState,
            $signCount,
            $this->createdAt,
            $lastUsedAt,
        );
    }
}
