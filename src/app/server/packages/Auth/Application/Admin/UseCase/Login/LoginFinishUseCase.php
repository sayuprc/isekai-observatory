<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Login;

use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\PasskeyCeremonyType;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\PasskeyAuthenticationResult;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use Support\Contracts\ClockInterface;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Exceptions\UnauthenticatedException;
use Throwable;

readonly class LoginFinishUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private PasskeyCeremonyStoreInterface $ceremonyStore,
        private AdminUserPasskeyRepositoryInterface $passkeyRepository,
        private PasskeyAuthenticatorInterface $passkeyAuthenticator,
        private RefreshTokenIssueService $refreshTokenIssueService,
        private AccessTokenIssueService $accessTokenIssueService,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private AuditLogRecorderInterface $recorder,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @throws UnauthenticatedException
     */
    public function handle(LoginFinishInputData $inputData): LoginFinishOutputData
    {
        $state = $this->ceremonyStore->pull($inputData->authCeremonyId);

        if (is_null($state) || $state->type !== PasskeyCeremonyType::Login) {
            throw new UnauthenticatedException();
        }

        $credentialId = $this->passkeyAuthenticator->credentialId($inputData->credential);

        if (is_null($credentialId)) {
            throw new UnauthenticatedException();
        }

        return $this->transaction->scope(
            fn (): LoginFinishOutputData => $this->authenticateAndPersist($state, $credentialId, $inputData->credential),
        );
    }

    /**
     * @param array<string, mixed> $credential
     */
    private function authenticateAndPersist(
        PasskeyCeremonyState $state,
        string $credentialId,
        array $credential,
    ): LoginFinishOutputData {
        $passkey = $this->passkeyRepository->findByAdminUserIdAndCredentialIdForUpdate($state->adminUserId, $credentialId);

        if (is_null($passkey)) {
            throw new UnauthenticatedException();
        }

        try {
            $verification = $this->passkeyAuthenticator->finishAuthentication(
                $credential,
                $state->optionsJson,
                $passkey,
                $passkey->userHandle,
            );
        } catch (Throwable) {
            throw new UnauthenticatedException();
        }

        if ($verification->credentialId !== $passkey->credentialId) {
            throw new UnauthenticatedException();
        }

        return $this->persist($state, $passkey, $verification);
    }

    private function persist(
        PasskeyCeremonyState $state,
        AdminUserPasskey $passkey,
        PasskeyAuthenticationResult $verification,
    ): LoginFinishOutputData {
        ['token' => $refreshToken, 'plainToken' => $plainRefreshToken] = $this->refreshTokenIssueService->issue($state->adminUserId);

        $updatedPasskey = $passkey->withCounter($verification->signCount, $this->clock->now());

        if (! $this->passkeyRepository->updateCounter($updatedPasskey, $passkey->signCount)) {
            throw new UnauthenticatedException();
        }

        $accessToken = $this->accessTokenIssueService->issue($refreshToken->refreshTokenId->value);

        $this->refreshTokenRepository->save($refreshToken);

        $this->recorder->record(
            AuditAction::Login,
            AuditTargetType::AdminUser,
            $refreshToken->adminUserId,
            [
                'refresh_token_id' => $refreshToken->refreshTokenId->value,
                'admin_user_passkey_id' => $passkey->adminUserPasskeyId,
            ],
            $refreshToken->adminUserId,
        );

        return new LoginFinishOutputData(
            $accessToken,
            $refreshToken->refreshTokenId->value,
            $plainRefreshToken,
        );
    }
}
