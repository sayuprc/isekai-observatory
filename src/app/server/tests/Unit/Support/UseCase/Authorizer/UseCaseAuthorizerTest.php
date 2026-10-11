<?php

declare(strict_types=1);

namespace Tests\Unit\Support\UseCase\Authorizer;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Permission;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use Auth\Infrastructures\Auth\UseCaseAuthorizationContext;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\Authorizer\UseCaseAuthorizer;
use Support\UseCase\Exceptions\PermissionDeniedException;
use Support\UseCase\Exceptions\UnauthenticatedException;
use Tests\TestCase;

class UseCaseAuthorizerTest extends TestCase
{
    #[Test]
    public function unauthenticatedWhenCurrentUserDoesNotExist(): void
    {
        $this->expectException(UnauthenticatedException::class);

        $this->createAuthorizer(new AuthContext())->authorize(Permission::ReadMedia);
    }

    #[Test]
    public function permissionDeniedWhenCurrentUserDoesNotHavePermission(): void
    {
        $context = new AuthContext();
        $context->set($this->createGeneralUser([]));

        $this->expectException(PermissionDeniedException::class);

        $this->createAuthorizer($context)->authorize(Permission::ReadMedia);
    }

    #[Test]
    public function returnsUserWhenCurrentUserHasPermission(): void
    {
        $context = new AuthContext();
        $context->set($this->createGeneralUser([Permission::ReadMedia->value]));

        $user = $this->createAuthorizer($context)->authorize(Permission::ReadMedia);

        $this->assertTrue($user->can(Permission::ReadMedia));
    }

    /**
     * @param list<string> $permissions
     */
    private function createGeneralUser(array $permissions): AdminUser
    {
        return AdminUser::reconstruct(
            $this->generateUuid(),
            'テストユーザー',
            'test@example.com',
            new DateTimeImmutable(),
            Role::General->value,
            $permissions,
        );
    }

    private function createAuthorizer(AuthContext $context): UseCaseAuthorizer
    {
        return new UseCaseAuthorizer(new UseCaseAuthorizationContext($context));
    }
}
