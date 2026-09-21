<?php

declare(strict_types=1);

namespace Auth\Domain\Services\RecoveryCode;

use AdminUser\Domain\Models\AdminUserId;
use Auth\Domain\Models\RecoveryCode\ConsumptionStatus;
use Auth\Domain\Models\RecoveryCode\HashedCodeValue;
use Auth\Domain\Models\RecoveryCode\RecoveryCode;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeId;
use Support\Contracts\Uuid\UuidGeneratorInterface;

class RecoveryCodeIssueService
{
    private const int CODE_COUNT = 10;

    public function __construct(
        private readonly UuidGeneratorInterface $uuidGenerator,
        private readonly RandomRecoveryCodeGeneratorInterface $randomRecoveryCodeGenerator,
        private readonly RecoveryCodeHasherInterface $recoveryCodeHasher,
    ) {
    }

    /**
     * @return array{codes: list<RecoveryCode>, plainCodes: list<string>}
     */
    public function issue(AdminUserId $adminUserId): array
    {
        $codes = [];
        $plainCodes = [];

        for ($i = 0; $i < self::CODE_COUNT; $i++) {
            $plainCode = $this->randomRecoveryCodeGenerator->generate();

            $codes[] = new RecoveryCode(
                new RecoveryCodeId($this->uuidGenerator->generate()),
                $adminUserId,
                new HashedCodeValue($this->recoveryCodeHasher->hash($plainCode)),
                ConsumptionStatus::Unused,
                null,
            );
            $plainCodes[] = $plainCode;
        }

        return [
            'codes' => $codes,
            'plainCodes' => $plainCodes,
        ];
    }
}
