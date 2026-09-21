<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\RegisterStart;

use AdminUser\Domain\Models\AdminUserName;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\RegistrationToken\RegistrationTokenConsumeService;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\PasskeyCeremonyType;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyUserHandleGeneratorInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;

readonly class RegisterStartUseCase
{
    private const string FAILED_MESSAGE = '登録に失敗しました。入力内容を確認してください。';

    public function __construct(
        private RegistrationTokenConsumeService $consumeService,
        private AdminUserIntegrityService $integrityService,
        private PasskeyAuthenticatorInterface $passkeyAuthenticator,
        private PasskeyUserHandleGeneratorInterface $userHandleGenerator,
        private PasskeyCeremonyStoreInterface $ceremonyStore,
        private UuidGeneratorInterface $uuidGenerator,
    ) {
    }

    public function handle(RegisterStartInputData $inputData): RegisterStartOutputData
    {
        $email = new Email($inputData->email);
        $name = new AdminUserName($inputData->name);

        $token = $this->consumeService->verify($inputData->plainToken, $email);

        if (is_null($token)) {
            throw new BusinessRuleViolationException(self::FAILED_MESSAGE);
        }

        try {
            $adminUser = $this->integrityService->prepareForCreate($name, $token->email, $token->role, $token->permissions);
        } catch (BusinessRuleViolationException) {
            // ユーザー列挙を防ぐため、失敗理由に依らず同一メッセージで返す
            throw new BusinessRuleViolationException(self::FAILED_MESSAGE);
        }

        $authCeremonyId = $this->uuidGenerator->generate();
        $startResult = $this->passkeyAuthenticator->startRegistration(
            $this->userHandleGenerator->generate(),
            $adminUser->email->value,
            $adminUser->name->value,
        );

        $this->ceremonyStore->put(new PasskeyCeremonyState(
            $authCeremonyId,
            PasskeyCeremonyType::Register,
            $adminUser->email->value,
            $adminUser->name->value,
            $adminUser->adminUserId->value,
            $startResult->optionsJson,
        ));

        return new RegisterStartOutputData($authCeremonyId, $startResult->publicKey);
    }
}
