<?php

declare(strict_types=1);

namespace Support\UseCase\Authorizer;

use AdminUser\Domain\Models\Permission;
use Support\UseCase\Exceptions\PermissionDeniedException;
use Support\UseCase\Exceptions\UnauthenticatedException;

readonly class UseCaseAuthorizer
{
    public function __construct(private AuthorizationContextInterface $context)
    {
    }

    /**
     * 認証・認可を検証し、失敗時は例外を投げる
     *
     * @throws UnauthenticatedException
     * @throws PermissionDeniedException
     */
    public function authorize(Permission $permission): AuthorizableUserInterface
    {
        $user = $this->context->currentUser();

        if (is_null($user)) {
            throw new UnauthenticatedException();
        }

        if (! $user->can($permission)) {
            throw new PermissionDeniedException();
        }

        return $user;
    }
}
