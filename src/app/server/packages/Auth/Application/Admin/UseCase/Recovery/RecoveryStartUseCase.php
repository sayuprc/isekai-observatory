<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Recovery;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\PasskeyCeremonyType;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyUserHandleGeneratorInterface;
use Auth\Domain\Services\RecoveryCode\RecoveryCodeVerifyService;
use Support\Contracts\Uuid\UuidGeneratorInterface;

readonly class RecoveryStartUseCase
{
    public function __construct(
        private AdminUserRepositoryInterface $adminUserRepository,
        private RecoveryCodeVerifyService $verifyService,
        private PasskeyAuthenticatorInterface $passkeyAuthenticator,
        private PasskeyUserHandleGeneratorInterface $userHandleGenerator,
        private PasskeyCeremonyStoreInterface $ceremonyStore,
        private UuidGeneratorInterface $uuidGenerator,
    ) {
    }

    public function handle(RecoveryStartInputData $inputData): RecoveryStartOutputData
    {
        $email = new Email($inputData->email);

        $adminUser = $this->adminUserRepository->findByEmail($email);

        // ユーザー列挙を防ぐため、メールの実在やコードの正否に依らず常に同一形状の
        // 登録 ceremony を返す。実在ユーザーかつコード検証成功時のみ本物の adminUserId と
        // 検証済みコードの id を束縛し、それ以外はダミーの adminUserId かつ recoveryCodeId
        // は null とすることで finish 時に必ず失敗させる
        // コードの消費は ceremony 完走時 (RecoveryFinish) に確定するためここでは検証のみ行う
        $adminUserId = $this->uuidGenerator->generate();
        $recoveryCodeId = null;

        if (! is_null($adminUser)) {
            $recoveryCode = $this->verifyService->verify($inputData->plainCode, $adminUser->adminUserId);

            if (! is_null($recoveryCode)) {
                $adminUserId = $adminUser->adminUserId->value;
                $recoveryCodeId = $recoveryCode->recoveryCodeId->value;
            }
        }

        $authCeremonyId = $this->uuidGenerator->generate();
        $startResult = $this->passkeyAuthenticator->startRegistration(
            $this->userHandleGenerator->generate(),
            $email->value,
            $inputData->name,
        );

        $this->ceremonyStore->put(new PasskeyCeremonyState(
            $authCeremonyId,
            PasskeyCeremonyType::Recovery,
            $email->value,
            $inputData->name,
            $adminUserId,
            $startResult->optionsJson,
            $recoveryCodeId,
        ));

        return new RecoveryStartOutputData($authCeremonyId, $startResult->publicKey);
    }
}
