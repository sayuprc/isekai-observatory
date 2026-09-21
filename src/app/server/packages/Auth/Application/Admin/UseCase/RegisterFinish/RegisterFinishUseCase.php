<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\RegisterFinish;

use AdminUser\Domain\Exceptions\DuplicateAdminUserEmailException;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserName;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\RegistrationToken\RegistrationTokenConsumeService;
use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\PasskeyCeremonyType;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyRegistrationResult;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use Support\Contracts\ClockInterface;
use Support\Contracts\TransactionInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Throwable;

readonly class RegisterFinishUseCase
{
    private const string FAILED_MESSAGE = '登録に失敗しました。入力内容を確認してください。';

    public function __construct(
        private TransactionInterface $transaction,
        private AdminUserRepositoryInterface $adminUserRepository,
        private AdminUserIntegrityService $integrityService,
        private RegistrationTokenConsumeService $consumeService,
        private RegistrationTokenRepositoryInterface $registrationTokenRepository,
        private PasskeyAuthenticatorInterface $passkeyAuthenticator,
        private PasskeyCeremonyStoreInterface $ceremonyStore,
        private AdminUserPasskeyRepositoryInterface $passkeyRepository,
        private RefreshTokenIssueService $refreshTokenIssueService,
        private AccessTokenIssueService $accessTokenIssueService,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private AuditLogRecorderInterface $recorder,
        private UuidGeneratorInterface $uuidGenerator,
        private ClockInterface $clock,
    ) {
    }

    public function handle(RegisterFinishInputData $inputData): RegisterFinishOutputData
    {
        $state = $this->ceremonyStore->pull($inputData->authCeremonyId);

        if (is_null($state) || $state->type !== PasskeyCeremonyType::Register) {
            throw new BusinessRuleViolationException(self::FAILED_MESSAGE);
        }

        if (is_null($state->name)) {
            throw new BusinessRuleViolationException(self::FAILED_MESSAGE);
        }

        try {
            $verification = $this->passkeyAuthenticator->finishRegistration(
                $inputData->credential,
                $state->optionsJson,
            );
        } catch (Throwable) {
            throw new BusinessRuleViolationException(self::FAILED_MESSAGE);
        }

        $name = $state->name;

        return $this->transaction->scope(
            fn (): RegisterFinishOutputData => $this->persist($state, $name, $inputData->plainToken, $verification),
        );
    }

    private function persist(
        PasskeyCeremonyState $state,
        string $name,
        string $plainToken,
        PasskeyRegistrationResult $verification,
    ): RegisterFinishOutputData {
        // state は start で検証済みの自前データのため、形式不正は不変条件違反として扱う
        $email = new Email($state->email);

        $token = $this->consumeService->verify($plainToken, $email);

        if (is_null($token)) {
            throw new BusinessRuleViolationException(self::FAILED_MESSAGE);
        }

        try {
            $adminUser = $this->integrityService->prepareForCreateWithId(
                new AdminUserId($state->adminUserId),
                new AdminUserName($name),
                $token->email,
                $token->role,
                $token->permissions,
            );
        } catch (BusinessRuleViolationException) {
            // ユーザー列挙を防ぐため、失敗理由に依らず同一メッセージで返す
            throw new BusinessRuleViolationException(self::FAILED_MESSAGE);
        }

        ['token' => $refreshToken, 'plainToken' => $plainRefreshToken] = $this->refreshTokenIssueService->issue(
            $adminUser->adminUserId->value,
        );

        try {
            $adminUser = $this->adminUserRepository->register($adminUser);
        } catch (DuplicateAdminUserEmailException) {
            throw new BusinessRuleViolationException(self::FAILED_MESSAGE);
        }

        $adminUserPasskeyId = $this->uuidGenerator->generate();

        $this->passkeyRepository->save(new AdminUserPasskey(
            $adminUserPasskeyId,
            $adminUser->adminUserId->value,
            $verification->userHandle,
            $name,
            $verification->credentialId,
            $verification->publicKey,
            $verification->aaguid,
            $verification->transports,
            $verification->backupEligible,
            $verification->backupState,
            $verification->signCount,
            $this->clock->now(),
            null,
        ));

        $this->registrationTokenRepository->save($token->consume());

        $accessToken = $this->accessTokenIssueService->issue($refreshToken->refreshTokenId->value);

        $this->refreshTokenRepository->save($refreshToken);

        $this->recorder->record(
            AuditAction::Register,
            AuditTargetType::AdminUser,
            $adminUser->adminUserId,
            [
                'admin_user_passkey_id' => $adminUserPasskeyId,
                'refresh_token_id' => $refreshToken->refreshTokenId->value,
            ],
            $adminUser->adminUserId,
        );

        return new RegisterFinishOutputData(
            $accessToken,
            $refreshToken->refreshTokenId->value,
            $plainRefreshToken,
        );
    }
}
