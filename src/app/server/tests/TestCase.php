<?php

declare(strict_types=1);

namespace Tests;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use Auth\Infrastructures\Auth\UseCaseAuthorizationContext;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\UseCase\Authorizer\UseCaseAuthorizer;

abstract class TestCase extends BaseTestCase
{
    private ?AuthContext $privilegedAuthContext = null;

    protected function generateUuid(): string
    {
        return $this->app->make(UuidGeneratorInterface::class)->generate();
    }

    protected function toUuid(string $bin): string
    {
        return $this->app->make(UuidConverterInterface::class)->toUuid($bin);
    }

    protected function privilegedContext(): AuthContext
    {
        if (! is_null($this->privilegedAuthContext)) {
            return $this->privilegedAuthContext;
        }

        $context = $this->app->make(AuthContext::class);

        $user = AdminUser::reconstruct(
            $this->generateUuid(),
            'テストユーザー',
            'test@example.com',
            new DateTimeImmutable(),
            Role::Privilege->value,
            [],
        );

        // 監査ログ機構が admin_users への外部キーを要求するため、
        // DB を使うテスト(DatabaseTransactions を使うテスト)の場合のみ、
        // 認証済みユーザーを実 DB にも登録する
        if (in_array(DatabaseTransactions::class, class_uses_recursive(static::class), true)) {
            $repository = $this->app->make(AdminUserRepositoryInterface::class);

            if (is_null($repository->find($user->adminUserId))) {
                $repository->register($user);
            }
        }

        $context->set($user);

        $this->privilegedAuthContext = $context;

        return $context;
    }

    protected function authorizer(?AuthContext $context = null): UseCaseAuthorizer
    {
        return new UseCaseAuthorizer(new UseCaseAuthorizationContext($context ?? $this->privilegedContext()));
    }
}
