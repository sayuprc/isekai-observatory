<?php

declare(strict_types=1);

namespace Auth\Domain\Services;

readonly class PasskeyRegistrationResult
{
    /**
     * @param list<string> $transports
     */
    public function __construct(
        public string $credentialId,
        public string $publicKey,
        public string $userHandle,
        public string $aaguid,
        public array $transports,
        public ?bool $backupEligible,
        public ?bool $backupState,
        public int $signCount,
    ) {
    }
}
