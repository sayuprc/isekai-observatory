<?php

declare(strict_types=1);

namespace Auth\Domain\Services\RecoveryCode;

use AdminUser\Domain\Models\AdminUserId;
use Auth\Domain\Models\RecoveryCode\RecoveryCode;
use Auth\Domain\Models\RecoveryCode\RecoveryCodeRepositoryInterface;
use SensitiveParameter;

class RecoveryCodeVerifyService
{
    public function __construct(
        private readonly RecoveryCodeHasherInterface $recoveryCodeHasher,
        private readonly RecoveryCodeRepositoryInterface $repository,
    ) {
    }

    /**
     * 平文コードに一致する利用可能なリカバリーコードを返す。なければ null
     */
    public function verify(#[SensitiveParameter] string $plainCode, AdminUserId $adminUserId): ?RecoveryCode
    {
        $codes = $this->repository->findUnusedByAdminUserIdForUpdate($adminUserId);

        foreach ($codes as $code) {
            if (! $this->recoveryCodeHasher->verify($plainCode, $code->code->value)) {
                continue;
            }

            if (! $code->isAvailable()) {
                return null;
            }

            return $code;
        }

        return null;
    }
}
