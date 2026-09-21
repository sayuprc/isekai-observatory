<?php

declare(strict_types=1);

namespace AdminUser\Application\Cli\UseCase\IssueRegistrationToken;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\RegistrationToken\RegistrationTokenIssueService;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;

readonly class IssueRegistrationTokenUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private AdminUserRepositoryInterface $adminUserRepository,
        private RegistrationTokenIssueService $issueService,
        private RegistrationTokenRepositoryInterface $repository,
    ) {
    }

    public function handle(IssueRegistrationTokenInputData $inputData): IssueRegistrationTokenOutputData
    {
        return $this->transaction->scope(function () use ($inputData): IssueRegistrationTokenOutputData {
            $email = new Email($inputData->email);
            $role = Role::fromValue($inputData->role);
            $permissions = Permissions::fromArray($inputData->permissions);

            if (! is_null($this->adminUserRepository->findByEmail($email))) {
                throw new BusinessRuleViolationException(sprintf('すでに使われているメールアドレスです "%s"', $inputData->email));
            }

            $issued = $this->issueService->issue($email, $role, $permissions);

            $token = $this->repository->save($issued['token']);

            return new IssueRegistrationTokenOutputData($token, $issued['plainToken']);
        });
    }
}
