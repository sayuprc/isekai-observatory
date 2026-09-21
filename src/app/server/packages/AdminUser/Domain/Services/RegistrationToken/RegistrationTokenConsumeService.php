<?php

declare(strict_types=1);

namespace AdminUser\Domain\Services\RegistrationToken;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use SensitiveParameter;
use Support\Contracts\ClockInterface;

class RegistrationTokenConsumeService
{
    public function __construct(
        private readonly ClockInterface $clock,
        private readonly TokenHasherInterface $tokenHasher,
        private readonly RegistrationTokenRepositoryInterface $repository,
    ) {
    }

    /**
     * 平文トークンに一致する利用可能な登録トークンを返す。なければ null
     */
    public function verify(#[SensitiveParameter] string $plainToken, Email $email): ?RegistrationToken
    {
        $tokens = $this->repository->findUnusedByEmailForUpdate($email);

        foreach ($tokens as $token) {
            if (! $this->tokenHasher->verify($plainToken, $token->token->value)) {
                continue;
            }

            if (! $token->isAvailable($this->clock->now())) {
                return null;
            }

            return $token;
        }

        return null;
    }
}
