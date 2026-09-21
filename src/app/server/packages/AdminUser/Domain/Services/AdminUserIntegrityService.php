<?php

declare(strict_types=1);

namespace AdminUser\Domain\Services;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserName;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\CreatedAt;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\Role;
use Support\Contracts\ClockInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;

class AdminUserIntegrityService
{
    public function __construct(
        private readonly ClockInterface $clock,
        private readonly UuidGeneratorInterface $generator,
        private readonly AdminUserRepositoryInterface $repository,
    ) {
    }

    /**
     * @throws BusinessRuleViolationException メールアドレスが使用済みの場合
     */
    public function prepareForCreate(AdminUserName $name, Email $email, Role $role, Permissions $permissions): AdminUser
    {
        if (! is_null($this->repository->findByEmail($email))) {
            throw new BusinessRuleViolationException(sprintf('すでに使われているメールアドレスです "%s"', $email->value));
        }

        return $this->build(new AdminUserId($this->generator->generate()), $name, $email, $role, $permissions);
    }

    /**
     * @throws BusinessRuleViolationException メールアドレスが使用済みの場合
     */
    public function prepareForCreateWithId(
        AdminUserId $adminUserId,
        AdminUserName $name,
        Email $email,
        Role $role,
        Permissions $permissions,
    ): AdminUser {
        if (! is_null($this->repository->findByEmailForUpdate($email))) {
            throw new BusinessRuleViolationException(sprintf('すでに使われているメールアドレスです "%s"', $email->value));
        }

        return $this->build($adminUserId, $name, $email, $role, $permissions);
    }

    private function build(
        AdminUserId $adminUserId,
        AdminUserName $name,
        Email $email,
        Role $role,
        Permissions $permissions,
    ): AdminUser {
        return new AdminUser(
            $adminUserId,
            $name,
            $email,
            new CreatedAt($this->clock->now()),
            $role,
            $permissions,
        );
    }
}
