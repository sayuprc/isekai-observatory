<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Login;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\PasskeyCeremonyType;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;

readonly class LoginStartUseCase
{
    public function __construct(
        private AdminUserRepositoryInterface $adminUserRepository,
        private AdminUserPasskeyRepositoryInterface $passkeyRepository,
        private PasskeyAuthenticatorInterface $passkeyAuthenticator,
        private PasskeyCeremonyStoreInterface $ceremonyStore,
        private UuidGeneratorInterface $uuidGenerator,
    ) {
    }

    public function handle(LoginStartInputData $inputData): LoginStartOutputData
    {
        $email = new Email($inputData->email);

        $adminUser = $this->adminUserRepository->findByEmail($email);
        $passkeys = is_null($adminUser)
            ? []
            : $this->passkeyRepository->findByAdminUserId($adminUser->adminUserId->value);

        // ユーザー列挙を防ぐため、メールの実在やパスキー登録の有無に依らず常に同一形状の
        // ceremony を返す。実在ユーザーのみ本物の adminUserId を束縛し、それ以外はダミーの
        // adminUserId にすることで finish 時に必ず認証失敗となる (応答は区別できない)
        $adminUserId = is_null($adminUser) || $passkeys === []
            ? $this->uuidGenerator->generate()
            : $adminUser->adminUserId->value;

        $authCeremonyId = $this->uuidGenerator->generate();
        $startResult = $this->passkeyAuthenticator->startAuthentication();

        $this->ceremonyStore->put(new PasskeyCeremonyState(
            $authCeremonyId,
            PasskeyCeremonyType::Login,
            $email->value,
            null,
            $adminUserId,
            $startResult->optionsJson,
        ));

        return new LoginStartOutputData($authCeremonyId, $startResult->publicKey);
    }
}
