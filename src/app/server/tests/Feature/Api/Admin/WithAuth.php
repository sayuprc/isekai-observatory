<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin;

use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use Auth\Infrastructures\Token\RefreshToken\RefreshTokenRepository;
use Tests\Support\Domain\EntityFactory;

trait WithAuth
{
    use EntityFactory;

    private function withAuth(): self
    {
        return $this->withRoleAuth(Role::Privilege, 'root@example.com');
    }

    private function withGeneralAuth(): self
    {
        return $this->withRoleAuth(Role::General, 'general@example.com');
    }

    private function withRoleAuth(Role $role, string $email): self
    {
        config()->set([
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $user = $this->createAdminUser($this->generateUuid(), $email, $role);

        $issued = $this->app->make(RefreshTokenIssueService::class)->issue($user->adminUserId->value);
        $refreshToken = $issued['token'];

        $this->app->make(AdminUserRepository::class)->register($user);
        $this->app->make(RefreshTokenRepository::class)->save($refreshToken);

        $accessToken = $this->app->make(AccessTokenIssueService::class)->issue($refreshToken->refreshTokenId->value);

        $authorization = 'Bearer ' . $accessToken->jwt->value;

        return $this
            ->withHeader('Authorization', $authorization)
            ->withServerVariables(['HTTP_AUTHORIZATION' => $authorization]);
    }
}
